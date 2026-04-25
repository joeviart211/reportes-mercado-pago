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
                    'operation_id'   => $data['ID DE OPERACIÓN EN MERCADO PAGO'],
                    'operation_type' => $data['TIPO DE OPERACIÓN'],
                ],
                [
                    'payment_method'   => $data['TIPO DE MEDIO DE PAGO'] ?: null,
                    'purchase_amount'  => (float) ($data['VALOR DE LA COMPRA'] ?? 0),
                    'commission'       => (float) ($data['COMISIONES + IVA'] ?? 0),
                    'net_amount'       => (float) ($data['MONTO NETO DE LA OPERACIÓN'] ?? 0),
                    'tax_retention'    => (float) ($data['IMPUESTOS COBRADOS POR RETENCIONES DE IIBB'] ?? 0),
                    'order_id'         => $data['ID DE LA ORDEN'] ?: null,
                    'shipment_id'      => $data['ID DEL ENVÍO'] ?: null,
                    'package_id'       => $data['ID DEL PAQUETE'] ?: null,
                    'sales_channel'    => $data['CANAL DE VENTA'] ?: null,
                    'payment_platform' => $data['PLATAFORMA DE COBRO'] ?: null,
                    'origin_at'        => !empty($data['FECHA DE ORIGEN'])
                                            ? \Carbon\Carbon::parse($data['FECHA DE ORIGEN']) : null,
                    'approved_at'      => !empty($data['FECHA DE APROBACIÓN'])
                                            ? \Carbon\Carbon::parse($data['FECHA DE APROBACIÓN']) : null,
                    'released_at'      => !empty($data['FECHA DE LIBERACIÓN DEL DINERO'])
                                            ? \Carbon\Carbon::parse($data['FECHA DE LIBERACIÓN DEL DINERO']) : null,
                ]
            );

            $count++;
        }

        return $count;
    }
}
