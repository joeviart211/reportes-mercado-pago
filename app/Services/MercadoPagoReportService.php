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
}
