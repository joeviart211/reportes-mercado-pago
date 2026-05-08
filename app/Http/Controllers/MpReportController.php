<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\MercadoPagoReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MpTransaction;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class MpReportController extends Controller
    {
    public function __construct(
        protected MercadoPagoReportService $reportService
    ) {}

    public function index(Branch $branch)
    {
        abort_unless($branch->isConnectedToMl(), 403, 'Sucursal no conectada.');

        $reports = $this->reportService->listReports($branch);

        // Verificar cuáles ya están importados
        $importedFiles = MpTransaction::where('branch_id', $branch->id)
            ->whereIn('file_name', collect($reports)->pluck('file_name'))
            ->pluck('file_name')
            ->toArray();

        return view('reports.index', compact('branch', 'reports', 'importedFiles'));
    }

    public function request(Request $request, Branch $branch)
    {
        abort_unless($branch->isConnectedToMl(), 403);

        $request->validate([
            'from' => 'required|date|before:to',
            'to'   => 'required|date|after:from',
        ]);

        $result = $this->reportService->requestReport(
            $branch,
            Carbon::parse($request->from)->startOfDay(),
            Carbon::parse($request->to)->endOfDay(),
        );

        return back()->with('info', "Reporte solicitado (ID: {$result['id']}). Estado: {$result['status']}.");
    }

    public function import(Branch $branch, string $fileName)
    {
        abort_unless($branch->isConnectedToMl(), 403);
       

        $csv   = $this->reportService->downloadReport($branch, $fileName);
    
       
        $count = $this->reportService->importCsv($branch, $csv, $fileName);

        return back()->with('success', "{$count} transacciones importadas para {$branch->name}.");
    }
    public function exportCsv(Branch $branch, string $fileName)
    {
        
        $rows = MpTransaction::where('branch_id', $branch->id)->where('file_name', $fileName)->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=mp-report-'.$branch->id.'.csv',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            // ─── Encabezados ───────────────────────────
            fputcsv($file, [
                'ID DE OPERACIÓN EN MERCADO PAGO',
                'TIPO DE OPERACIÓN
',
                'Referencia externa',

                ' MEDIO DE PAGO',
                'TIPO DE MEDIO DE PAGO',
                'CUOTAS',

                'Monto de compra',
                'Monto del vendedor',
                'Monto real',
                'Monto de cupón',

                'Comisión +IVA',
                'Tarifa de marketplace',
                'Tarifa de financiamiento',
                'Tarifa de envío',

                'Monto neto',
                'Retención de impuestos',

                'Moneda de transacción',
                'Moneda de liquidación',

                'ID de orden',
                'ID de envío',
                'ID de paquete',

                'Canal de ventas',
                'ID de tienda',
                'ID de POS',
                'Nombre de POS',

                'Modalidad de envío',

                'Fecha de origen',
                'Fecha de liberación'
            ]);

            // ─── Filas ────────────────────────────────
            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->operation_id,
                    $row->operation_type,
                    $row->external_reference,

                    $row->payment_method,
                    $row->payment_method_type,
                    $row->installments,

                    $row->purchase_amount,
                    $row->seller_amount,
                    $row->real_amount,
                    $row->coupon_amount,

                    $row->commission,
                    $row->mkp_fee,
                    $row->financing_fee,
                    $row->shipping_fee,

                    $row->net_amount,
                    $row->tax_retention,

                    $row->transaction_currency,
                    $row->settlement_currency,

                    $row->order_id,
                    $row->shipment_id,
                    $row->package_id,

                    $row->sales_channel,
                    $row->store_id,
                    $row->pos_id,
                    $row->pos_name,

                    $row->shipment_mode,

                    optional($row->origin_at)->toDateTimeString(),
                    optional($row->released_at)->toDateTimeString(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function exportXls(Branch $branch, string $fileName)
    {
        $rows = MpTransaction::where('branch_id', $branch->id)
            ->where('file_name', $fileName)
            ->orderBy('origin_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        /*
        |--------------------------------------------------------------------------
        | Encabezados
        |--------------------------------------------------------------------------
        */

        $headers = [

            'ID DE OPERACIÓN EN MERCADO PAGO',
            'TIPO DE OPERACIÓN',
            'STATUS API',
            'DETALLE STATUS API',

            'Referencia externa',

            'MEDIO DE PAGO',
            'TIPO DE MEDIO DE PAGO',

            'CUOTAS',

            'Monto de compra',
            'Monto del vendedor',
            'Monto real',
            'Monto de cupón',

            'Comisión + IVA',
            'Tarifa de marketplace',
            'Tarifa de financiamiento',
            'Tarifa de envío',

            'Monto neto',
            'Retención de impuestos',

            'Moneda de transacción',
            'Moneda de liquidación',

            'ID de orden',
            'ID de envío',
            'ID de paquete',

            'Canal de ventas',

            'ID de tienda',
            'ID de POS',
            'Nombre de POS',

            'Modalidad de envío',

            'Plataforma de cobro',

            'Fecha de origen',
            'Fecha de aprobación',
            'Fecha de liberación del dinero',

            'Archivo fuente',
        ];

        /*
        |--------------------------------------------------------------------------
        | Columnas texto (evitar notación científica)
        |--------------------------------------------------------------------------
        */

        $textColumns = [
            'A', // operation_id
            'E', // external_reference
            'U', // order_id
            'V', // shipment_id
            'W', // package_id
            'Y', // pos_id
        ];

        $sheet->fromArray($headers, null, 'A1');

        /*
        |--------------------------------------------------------------------------
        | Filas
        |--------------------------------------------------------------------------
        */

        foreach ($rows as $i => $row) {

            $rowNum = $i + 2;

            $data = [

                $row->operation_id,
                $row->operation_type,

                $row->api_status ?? null,
                $row->api_status_detail ?? null,

                $row->external_reference,

                $row->payment_method,
                $row->payment_method_type,

                $row->installments,

                $row->purchase_amount,
                $row->seller_amount,
                $row->real_amount,
                $row->coupon_amount,

                $row->commission,
                $row->mkp_fee,
                $row->financing_fee,
                $row->shipping_fee,

                $row->net_amount,
                $row->tax_retention,

                $row->transaction_currency,
                $row->settlement_currency,

                $row->order_id,
                $row->shipment_id,
                $row->package_id,

                $row->sales_channel,

                $row->store_id,
                $row->pos_id,
                $row->pos_name,

                $row->shipment_mode,

                $row->payment_platform,

                optional($row->origin_at)->toDateTimeString(),

                optional($row->approved_at)->toDateTimeString(),

                optional($row->released_at)->toDateTimeString(),

                $row->file_name,
            ];

            foreach ($data as $colIndex => $value) {

                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);

                $cell = $sheet->getCell("{$colLetter}{$rowNum}");

                /*
                |--------------------------------------------------------------------------
                | Forzar texto
                |--------------------------------------------------------------------------
                */

                if (in_array($colLetter, $textColumns)) {

                    $cell->setValueExplicit(
                        (string) $value,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );

                } else {

                    $cell->setValue($value);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Estilo encabezados
        |--------------------------------------------------------------------------
        */

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([

            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold' => true,
            ],

            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A90D9'],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Auto ancho columnas
        |--------------------------------------------------------------------------
        */

        for ($colIndex = 1; $colIndex <= count($headers); $colIndex++) {

            $col = Coordinate::stringFromColumnIndex($colIndex);

            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        /*
        |--------------------------------------------------------------------------
        | Freeze header
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane('A2');

        /*
        |--------------------------------------------------------------------------
        | Crear archivo
        |--------------------------------------------------------------------------
        */

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'mp_report_');

        $writer->save($tempFile);

        return response()->download(
            $tempFile,
            'mp-report-' . $branch->id . '.xlsx',
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]
        )->deleteFileAfterSend(true);
    }
}