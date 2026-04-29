<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\MercadoPagoReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MpTransaction;

class MpReportController extends Controller
{
    public function __construct(
        protected MercadoPagoReportService $reportService
    ) {}

    public function index(Branch $branch)
    {
        abort_unless($branch->isConnectedToMl(), 403, 'Sucursal no conectada.');

        $reports = $this->reportService->listReports($branch);

        return view('reports.index', compact('branch', 'reports'));
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
        $count = $this->reportService->importCsv($branch, $csv);

        return back()->with('success', "{$count} transacciones importadas para {$branch->name}.");
    }
    public function exportCsv(Branch $branch)
    {
        $rows = MpTransaction::where('branch_id', $branch->id)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=mp-report-'.$branch->id.'.csv',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, [
                'operation_id',
                'type',
                'amount',
                'commission',
                'net',
                'order_id',
                'shipment_id',
                'date'
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->operation_id,
                    $row->operation_type,
                    $row->purchase_amount,
                    $row->commission,
                    $row->net_amount,
                    $row->order_id,
                    $row->shipment_id,
                    optional($row->origin_at)->toDateTimeString(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}