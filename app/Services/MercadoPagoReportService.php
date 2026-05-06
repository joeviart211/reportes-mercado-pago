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
    public function downloadReport(Branch $branch, string $fileName): string
    {
        $token = $this->authService->getValidToken($branch);

        $this->logCurrentUser($token, 'DOWNLOAD REPORT', $branch->ml_client_id);

        $response = Http::withToken($token)
            ->get(self::MP_BASE . "/v1/account/settlement_report/{$fileName}");
        Log::info('Settlement response body', [
            'body' => $response->body()
        ]);

        return $response->throw()->body(); // CSV en texto plano
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
    public function importCsv(Branch $branch, string $csvContent, string $fileName): int
        {
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $csvContent);
            rewind($stream);

            $headers = null;
            $count   = 0;

            while (($columns = fgetcsv($stream, 0, ',', '"', '\\')) !== false) {
                if (!$headers) {
                    $headers = array_map('trim', $columns);
                    continue;
                }

                if (count($columns) !== count($headers)) {
                    logger()->warning('CSV row column mismatch', [
                        'expected' => count($headers),
                        'got'      => count($columns),
                    ]);
                    continue;
                }

                $data = array_combine($headers, $columns);

                DB::enableQueryLog();
                $modelInstance = new \App\Models\MpTransaction([
                    'branch_id'  => $branch->id,
                    'file_name'  => $fileName,
                    'operation_id' => $data['SOURCE_ID'],
                ]);

                logger()->info('ATTRIBUTES BEFORE SAVE', $modelInstance->getAttributes());
                logger()->info('FILLABLE', $modelInstance->getFillable());

                $modelInstance->save();

                \App\Models\MpTransaction::create(
                    [
                        'branch_id'      => $branch->id,
                        'operation_id'   => $data['SOURCE_ID'],
                        'operation_type' => $data['TRANSACTION_TYPE'],
                        'file_name'   => $fileName,
                        // ─── Identificación ────────────────────────
                        'external_reference' => $data['EXTERNAL_REFERENCE'] ?: null,

                        // ─── Pago ──────────────────────────────────
                        'payment_method'      => $data['PAYMENT_METHOD'] ?: null,
                        'payment_method_type' => $data['PAYMENT_METHOD_TYPE'] ?: null,
                        'installments'        => (int) ($data['INSTALLMENTS'] ?? 0),

                        // ─── Montos ────────────────────────────────
                        'purchase_amount' => (float) ($data['TRANSACTION_AMOUNT'] ?? 0),
                        'seller_amount'   => (float) ($data['SELLER_AMOUNT'] ?? 0),
                        'real_amount'     => (float) ($data['REAL_AMOUNT'] ?? 0),
                        'coupon_amount'   => (float) ($data['COUPON_AMOUNT'] ?? 0),

                        // ─── Comisiones ────────────────────────────
                        'commission'     => (float) ($data['FEE_AMOUNT'] ?? 0),
                        'mkp_fee'        => (float) ($data['MKP_FEE_AMOUNT'] ?? 0),
                        'financing_fee'  => (float) ($data['FINANCING_FEE_AMOUNT'] ?? 0),
                        'shipping_fee'   => (float) ($data['SHIPPING_FEE_AMOUNT'] ?? 0),

                        // ─── Neto e impuestos ──────────────────────
                        'net_amount'    => (float) ($data['SETTLEMENT_NET_AMOUNT'] ?? 0),
                        'tax_retention' => (float) ($data['TAXES_AMOUNT'] ?? 0),

                        // ─── JSON ──────────────────────────────────
                        'tax_detail' => !empty($data['TAX_DETAIL'])
                            ? json_decode($data['TAX_DETAIL'], true)
                            : null,

                        'metadata' => !empty($data['METADATA'])
                            ? json_decode($data['METADATA'], true)
                            : null,

                        // ─── Monedas ───────────────────────────────
                        'transaction_currency' => $data['TRANSACTION_CURRENCY'] ?: null,
                        'settlement_currency'  => $data['SETTLEMENT_CURRENCY'] ?: null,

                        // ─── Relación ML ───────────────────────────
                        'order_id'    => $data['ORDER_ID'] ?: null,
                        'shipment_id' => $data['SHIPPING_ID'] ?: null,
                        'package_id'  => $data['PACK_ID'] ?: null,

                        // ─── Canal / POS ───────────────────────────
                        'sales_channel' => $data['STORE_NAME'] ?: null,
                        'store_id'      => $data['STORE_ID'] ?: null,
                        'pos_id'        => $data['POS_ID'] ?: null,
                        'pos_name'      => $data['POS_NAME'] ?: null,

                        // ─── Logística ─────────────────────────────
                        'shipment_mode' => $data['SHIPMENT_MODE'] ?: null,

                        // ─── Metadata extra ────────────────────────
                        'operation_tags' => $data['OPERATION_TAGS'] ?: null,

                        // ─── Plataforma ────────────────────────────
                        'payment_platform' => $data['POI_WALLET_NAME']
                            ?: ($data['PAYMENT_METHOD'] ?: null),

                        // ─── Fechas ────────────────────────────────
                        'origin_at' => !empty($data['TRANSACTION_DATE'])
                            ? \Carbon\Carbon::parse($data['TRANSACTION_DATE']) : null,

                        'approved_at' => !empty($data['TRANSACTION_DATE'])
                            ? \Carbon\Carbon::parse($data['TRANSACTION_DATE']) : null,

                        'released_at' => !empty($data['SETTLEMENT_DATE'])
                            ? \Carbon\Carbon::parse($data['SETTLEMENT_DATE']) : null,
                       
                    ],
                );
                dd(\DB::getQueryLog());
                $count++;
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
}
