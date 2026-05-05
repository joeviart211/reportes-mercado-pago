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
                'operation_id',
                'operation_type',
                'external_reference',

                'payment_method',
                'payment_method_type',
                'installments',

                'purchase_amount',
                'seller_amount',
                'real_amount',
                'coupon_amount',

                'commission',
                'mkp_fee',
                'financing_fee',
                'shipping_fee',

                'net_amount',
                'tax_retention',

                'transaction_currency',
                'settlement_currency',

                'order_id',
                'shipment_id',
                'package_id',

                'sales_channel',
                'store_id',
                'pos_id',
                'pos_name',

                'shipment_mode',

                'origin_at',
                'released_at'
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
}