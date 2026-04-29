<?php

namespace App\Services;


use App\Models\Branch;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Services\MercadoLibreAuthService;
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

        $response = Http::withToken($token)
            ->get(self::MP_BASE . "/v1/account/settlement_report/{$fileName}");

        return $response->throw()->body(); // CSV en texto plano
    }

    /**
     * Reporte de Liberaciones (alternativa)
     * GET /v1/account/release_report/{file_name}
     */
    public function downloadReleaseReport(Branch $branch, string $fileName): string
    {
        $token = $this->authService->getValidToken($branch);

        $response = Http::withToken($token)
            ->get(self::MP_BASE . "/v1/account/release_report/{$fileName}");

        return $response->throw()->body();
    }
    // ─── Procesar e importar CSV a la BD ──────────────────────────
    public function importCsv(Branch $branch, string $csvContent): int
        {
            $rows    = array_filter(explode("\n", trim($csvContent)));
            $headers = null;
            $count   = 0;

            foreach ($rows as $row) {
                $columns = str_getcsv($row);

                if (!$headers) {
                    $headers = array_map('trim', $columns);
                    continue;
                }

                if (count($columns) !== count($headers)) {
                    continue;
                }

                $data = array_combine($headers, $columns);

                \App\Models\MpTransaction::updateOrCreate(
                    [
                        'branch_id'      => $branch->id,
                        'operation_id'   => $data['SOURCE_ID'],
                        'operation_type' => $data['TRANSACTION_TYPE'],
                    ],
                    [
                        'payment_method'   => $data['PAYMENT_METHOD_TYPE'] ?: null,
                        'purchase_amount'  => (float) ($data['TRANSACTION_AMOUNT'] ?? 0),
                        'commission'       => (float) ($data['FEE_AMOUNT'] ?? 0),
                        'net_amount'       => (float) ($data['SETTLEMENT_NET_AMOUNT'] ?? 0),
                        'tax_retention'    => (float) ($data['TAXES_AMOUNT'] ?? 0),
                        'order_id'         => $data['ORDER_ID'] ?: null,
                        'shipment_id'      => $data['SHIPPING_ID'] ?: null,
                        'package_id'       => $data['PACK_ID'] ?: null,
                        'sales_channel'    => $data['STORE_NAME'] ?: null,
                        'payment_platform' => $data['POI_WALLET_NAME'] ?: ($data['PAYMENT_METHOD'] ?: null),
                        'origin_at'        => !empty($data['TRANSACTION_DATE'])
                                                ? \Carbon\Carbon::parse($data['TRANSACTION_DATE']) : null,
                        'approved_at'      => !empty($data['TRANSACTION_DATE'])
                                                ? \Carbon\Carbon::parse($data['TRANSACTION_DATE']) : null,
                        'released_at'      => !empty($data['SETTLEMENT_DATE'])
                                                ? \Carbon\Carbon::parse($data['SETTLEMENT_DATE']) : null,
                    ]
                );

                $count++;
            }

            return $count;
        }
}
