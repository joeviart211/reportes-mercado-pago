<?php

namespace App\Services;


use App\Models\Branch;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Services\MercadoLibreAuthService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MercadoPagoReportService
{
    const MP_BASE = 'https://api.mercadopago.com';

    public function __construct(
        protected MercadoLibreAuthService $authService
    ) {}

    /**
     * Paso 1: Solicitar generación del reporte por rango de fechas
     * POST /v1/account/settlement_report
     */
    public function requestReport(Branch $branch, Carbon $from, Carbon $to): array
    {
        $token = $this->authService->getValidToken($branch);


        $response = Http::withToken($token)
            ->post(self::MP_BASE . '/v1/account/settlement_report', [
                'begin_date' => $from->utc()->toIso8601String(),
                'end_date'   => $to->utc()->toIso8601String(),
            ]);

        return $response->throw()->json();
        // Retorna: id, status (pending → processed), file_name
    }

    /**
     * Paso 2: Buscar reportes disponibles
     * GET /v1/account/settlement_report/search
     */
    public function listReports(Branch $branch, array $filters = []): array
    {
        $token = $this->authService->getValidToken($branch);

        $this->logCurrentUser($token, 'LIST REPORTS', $branch->ml_client_id);

        $response = Http::withToken($token)
            ->get(self::MP_BASE . '/v1/account/settlement_report/search', $filters);

        return $response->throw()->json()['results'] ?? [];
    }

    /**
     * Paso 3: Descargar el CSV una vez procesado
     * GET /v1/account/settlement_report/{file_name}
     */
    public function downloadReport(Branch $branch, string $fileName): array
    {
        $token = $this->authService->getValidToken($branch);

        $this->logCurrentUser($token, 'DOWNLOAD REPORT', $branch->ml_client_id);

        $response = Http::withToken($token)
            ->get(self::MP_BASE . "/v1/account/settlement_report/{$fileName}");

        $csv = $response->throw()->body();

        Log::info('Settlement report downloaded');

        /*
        |--------------------------------------------------------------------------
        | Parse CSV correctamente
        |--------------------------------------------------------------------------
        */

        $handle = fopen('php://temp', 'r+');

        fwrite($handle, $csv);

        rewind($handle);

        $header = fgetcsv($handle);

        if (!$header) {
            return [];
        }

        // Limpiar BOM UTF-8
        $header = array_map(function ($value) {
            return trim(str_replace("\xEF\xBB\xBF", '', $value));
        }, $header);

        $data = [];

        while (($row = fgetcsv($handle)) !== false) {

            /*
            |--------------------------------------------------------------------------
            | Ignorar filas vacías
            |--------------------------------------------------------------------------
            */

            if (empty(array_filter($row))) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Validar columnas
            |--------------------------------------------------------------------------
            */

            if (count($header) !== count($row)) {

                Log::warning('Malformed CSV row', [
                    'header_count' => count($header),
                    'row_count' => count($row),
                    'row' => $row,
                ]);

                continue;
            }

            $item = array_combine($header, $row);

            $paymentId = $item['SOURCE_ID'] ?? null;

            $paymentDetail = null;

            /*
            |--------------------------------------------------------------------------
            | Consultar detalle payment
            |--------------------------------------------------------------------------
            */

            if ($paymentId) {

                try {

                    $paymentResponse = Http::withToken($token)
                        ->get(self::MP_BASE . "/v1/payments/{$paymentId}");

                    if ($paymentResponse->successful()) {

                        $paymentDetail = $paymentResponse->json();

                    } else {

                        Log::warning('Payment detail failed', [
                            'payment_id' => $paymentId,
                            'status' => $paymentResponse->status(),
                            'body' => $paymentResponse->body(),
                        ]);
                    }

                    // Evitar rate limit
                    usleep(300000);

                } catch (\Exception $e) {

                    Log::error('Payment request exception', [
                        'payment_id' => $paymentId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Construir data final
            |--------------------------------------------------------------------------
            */

            $data[] = [

                'operation_id' => $paymentId,

                'payment_type' => $item['PAYMENT_METHOD_TYPE'] ?? null,

                'transaction_type' => $item['TRANSACTION_TYPE'] ?? null,

                'purchase_amount' => $item['TRANSACTION_AMOUNT'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | Fechas
                |--------------------------------------------------------------------------
                */

                'origin_at' => $paymentDetail['date_created']
                    ?? $item['TRANSACTION_DATE']
                    ?? null,

                'approved_at' => $paymentDetail['date_approved']
                    ?? $item['SETTLEMENT_DATE']
                    ?? null,

                'released_at' => $paymentDetail['money_release_date']
                    ?? $item['MONEY_RELEASE_DATE']
                    ?? null,

                /*
                |--------------------------------------------------------------------------
                | Importes
                |--------------------------------------------------------------------------
                */

                'commission' => $item['FEE_AMOUNT'] ?? null,

                'net_amount' => $item['SETTLEMENT_NET_AMOUNT'] ?? null,

                'tax_retention' => $item['TAXES_AMOUNT'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | Relaciones ML
                |--------------------------------------------------------------------------
                */

                'order_id' => $item['ORDER_ID'] ?? null,

                'shipment_id' => $item['SHIPPING_ID'] ?? null,

                'package_id' => $item['PACK_ID'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | Otros
                |--------------------------------------------------------------------------
                */

                'sales_channel' => $item['SITE'] ?? null,

                'payment_platform' => $item['PAYMENT_METHOD'] ?? null,

                'raw_csv' => $item,

                'payment_api_response' => $paymentDetail,
            ];
        }

        fclose($handle);       

        return $data;
    }

    /**
     * Reporte de Liberaciones (alternativa)
     * GET /v1/account/release_report/{file_name}
     */
    public function downloadReleaseReport(Branch $branch, string $fileName): string
    {
        $token = $this->authService->getValidToken($branch);
        $this->logCurrentUser($token, 'DOWNLOAD RELEASE REPORT', $branch->ml_client_id);
        $response = Http::withToken($token)
            ->get(self::MP_BASE . "/v1/account/release_report/{$fileName}");

        return $response->throw()->body();
    }
    // ─── Procesar e importar CSV a la BD ──────────────────────────
    public function importCsv(Branch $branch, array $rows, string $fileName): int
{
    $count = 0;

    foreach ($rows as $data) {

        try {

            logger()->info('ROW OK', [
                'SOURCE_ID'        => $data['operation_id'] ?? 'MISSING',
                'TRANSACTION_TYPE' => $data['transaction_type'] ?? 'MISSING',
                'file_name'        => $fileName,
            ]);

            \App\Models\MpTransaction::updateOrCreate(

                [
                    'branch_id'    => $branch->id,
                    'operation_id' => $data['operation_id'],
                ],

                [

                    'file_name' => $fileName,

                    /*
                    |--------------------------------------------------------------------------
                    | Identificación
                    |--------------------------------------------------------------------------
                    */

                    'external_reference' => $data['raw_csv']['EXTERNAL_REFERENCE'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Operación
                    |--------------------------------------------------------------------------
                    */

                    'operation_type' => $data['transaction_type'] ?? null,

                    'payment_method' => $data['payment_platform'] ?? null,

                    'payment_method_type' => $data['payment_type'] ?? null,

                    'installments' => (int) (
                        $data['raw_csv']['INSTALLMENTS'] ?? 0
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | Montos
                    |--------------------------------------------------------------------------
                    */

                    'purchase_amount' => (float) (
                        $data['purchase_amount'] ?? 0
                    ),

                    'seller_amount' => (float) (
                        $data['raw_csv']['SELLER_AMOUNT'] ?? 0
                    ),

                    'real_amount' => (float) (
                        $data['raw_csv']['REAL_AMOUNT'] ?? 0
                    ),

                    'coupon_amount' => (float) (
                        $data['raw_csv']['COUPON_AMOUNT'] ?? 0
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | Comisiones
                    |--------------------------------------------------------------------------
                    */

                    'commission' => (float) (
                        $data['commission'] ?? 0
                    ),

                    'mkp_fee' => (float) (
                        $data['raw_csv']['MKP_FEE_AMOUNT'] ?? 0
                    ),

                    'financing_fee' => (float) (
                        $data['raw_csv']['FINANCING_FEE_AMOUNT'] ?? 0
                    ),

                    'shipping_fee' => (float) (
                        $data['raw_csv']['SHIPPING_FEE_AMOUNT'] ?? 0
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | Neto e impuestos
                    |--------------------------------------------------------------------------
                    */

                    'net_amount' => (float) (
                        $data['net_amount'] ?? 0
                    ),

                    'tax_retention' => (float) (
                        $data['tax_retention'] ?? 0
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | JSON
                    |--------------------------------------------------------------------------
                    */

                    'tax_detail' => !empty($data['raw_csv']['TAX_DETAIL'])
                        ? json_decode($data['raw_csv']['TAX_DETAIL'], true)
                        : null,

                    'metadata' => !empty($data['raw_csv']['METADATA'])
                        ? json_decode($data['raw_csv']['METADATA'], true)
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Monedas
                    |--------------------------------------------------------------------------
                    */

                    'transaction_currency' => $data['raw_csv']['TRANSACTION_CURRENCY'] ?? null,

                    'settlement_currency' => $data['raw_csv']['SETTLEMENT_CURRENCY'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Relaciones Mercado Libre
                    |--------------------------------------------------------------------------
                    */

                    'order_id' => $data['order_id'] ?? null,

                    'shipment_id' => $data['shipment_id'] ?? null,

                    'package_id' => $data['package_id'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Canal / POS
                    |--------------------------------------------------------------------------
                    */

                    'sales_channel' => $data['sales_channel'] ?? null,

                    'store_id' => $data['raw_csv']['STORE_ID'] ?? null,

                    'store_name' => $data['raw_csv']['STORE_NAME'] ?? null,

                    'pos_id' => $data['raw_csv']['POS_ID'] ?? null,

                    'pos_name' => $data['raw_csv']['POS_NAME'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Logística
                    |--------------------------------------------------------------------------
                    */

                    'shipment_mode' => $data['raw_csv']['SHIPMENT_MODE'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Metadata extra
                    |--------------------------------------------------------------------------
                    */

                    'operation_tags' => $data['raw_csv']['OPERATION_TAGS'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Plataforma
                    |--------------------------------------------------------------------------
                    */

                    'payment_platform' => $data['payment_platform'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Nuevas columnas API
                    |--------------------------------------------------------------------------
                    */

                    'api_payment_id' => $data['payment_api_response']['id'] ?? null,

                    'api_status' => $data['payment_api_response']['status'] ?? null,

                    'api_status_detail' => $data['payment_api_response']['status_detail'] ?? null,

                    'api_money_release_date' => $data['payment_api_response']['money_release_date'] ?? null,

                    'api_date_created' => $data['payment_api_response']['date_created'] ?? null,

                    'api_date_approved' => $data['payment_api_response']['date_approved'] ?? null,

                    'api_response' => $data['payment_api_response'] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Fechas
                    |--------------------------------------------------------------------------
                    */

                    'origin_at' => !empty($data['origin_at'])
                        ? \Carbon\Carbon::parse($data['origin_at'])
                        : null,

                    'approved_at' => !empty($data['approved_at'])
                        ? \Carbon\Carbon::parse($data['approved_at'])
                        : null,

                    'released_at' => !empty($data['released_at'])
                        ? \Carbon\Carbon::parse($data['released_at'])
                        : null,
                ]
            );

            $count++;

        } catch (\Exception $e) {

            logger()->error('Import CSV row failed', [

                'operation_id' => $data['operation_id'] ?? null,

                'error' => $e->getMessage(),

                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    return $count;
}
    private function logCurrentUser(string $token, string $context = 'MP DEBUG', $client_id): void
    {
        try {
            $response = Http::withToken($token)
                ->get(self::MP_BASE . '/users/me');

            if ($response->ok()) {
                $data = $response->json();

                Log::info($context, [
                    'client_id' => $client_id,
                    'user_id' => $data['id'] ?? null,
                    'nickname' => $data['nickname'] ?? null,
                    'email' => $data['email'] ?? null,
                    'site_id' => $data['site_id'] ?? null,
                ]);
            } else {
                Log::warning($context . ' - failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error($context . ' - exception', [
                'message' => $e->getMessage()
            ]);
        }
    }
    private function parseCsvLine(string $line): array
{
    $fields    = [];
    $field     = '';
    $inQuote   = false;
    $jsonDepth = 0; // rastrea profundidad de { } dentro del campo
    $len       = strlen($line);
    $i         = 0;

    while ($i < $len) {
        $char = $line[$i];

        if (!$inQuote) {
            if ($char === '"') {
                $inQuote   = true;
                $jsonDepth = 0;
                $i++;
                continue;
            }
            if ($char === ',') {
                $fields[] = $field;
                $field    = '';
                $i++;
                continue;
            }
            $field .= $char;
            $i++;
            continue;
        }

        // Dentro de comillas
        if ($char === '{') {
            $jsonDepth++;
            $field .= $char;
            $i++;
            continue;
        }

        if ($char === '}') {
            $jsonDepth--;
            $field .= $char;
            $i++;
            continue;
        }

        if ($char === '"') {
            // RFC 4180: "" = comilla escapada
            if (isset($line[$i + 1]) && $line[$i + 1] === '"') {
                $field .= '"';
                $i += 2;
                continue;
            }

            // Si estamos dentro de un JSON (jsonDepth > 0) → comilla interna malformada
            if ($jsonDepth > 0) {
                $field .= '"';
                $i++;
                continue;
            }

            // jsonDepth === 0 → cierre real del campo
            $inQuote = false;
            $i++;
            continue;
        }

        $field .= $char;
        $i++;
    }

    $fields[] = $field;
    return $fields;
}
    
}
