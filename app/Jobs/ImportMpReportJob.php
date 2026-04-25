<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Branch;
use app\Services\MercadoPagoReportService;
use App\Models\MpTransaction;
use Carbon\Carbon;

// app/Jobs/ImportMpReportJob.php

class ImportMpReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Branch $branch,
        public readonly string $fileName
    ) {}

    public function handle(MercadoPagoReportService $reportService): void
    {
        // 1. Descargar CSV
        $csv = $reportService->downloadReport($this->branch, $this->fileName);

        // 2. Parsear línea por línea (saltando encabezado)
        $rows = array_filter(explode("\n", $csv));
        $headers = null;

        foreach ($rows as $row) {
            $columns = str_getcsv($row);

            if (!$headers) {
                $headers = $columns;
                continue;
            }

            $data = array_combine($headers, $columns);

            // 3. Insertar/actualizar evitando duplicados (unique en migration)
            MpTransaction::updateOrCreate(
                [
                    'branch_id'      => $this->branch->id,
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
                    'origin_at'        => $data['FECHA DE ORIGEN'] ? Carbon::parse($data['FECHA DE ORIGEN']) : null,
                    'approved_at'      => $data['FECHA DE APROBACIÓN'] ? Carbon::parse($data['FECHA DE APROBACIÓN']) : null,
                    'released_at'      => $data['FECHA DE LIBERACIÓN DEL DINERO'] ? Carbon::parse($data['FECHA DE LIBERACIÓN DEL DINERO']) : null,
                ]
            );
        }
    }
}