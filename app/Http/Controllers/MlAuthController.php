<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Services\MercadoLibreAuthService;
use App\Services\MercadoPagoReportService;
use App\Http\Controllers\Controller;

class MlAuthController extends Controller
{
    public function redirect(Branch $branch, MercadoLibreAuthService $auth)
    {
        return redirect($auth->getAuthorizationUrl($branch));
    }

    public function callback(Request $request)
{
    abort_unless($request->filled('code') && $request->filled('state'), 400);

    $branchId = (int) str_replace('branch_', '', $request->state);
    $branch   = Branch::findOrFail($branchId);

    // Ahora le pasamos la sucursal para que use SUS llaves
    $tokens = $this->authService->exchangeCodeForTokens($branch, $request->code);

    $this->authService->storeTokens($branch, $tokens);

    return redirect()
        ->route('branches.reports.index', $branch)
        ->with('success', 'Mercado Libre conectado.');
}
}

// app/Http/Controllers/MpReportController.php

class MpReportController extends Controller
{
    public function __construct(
        protected MercadoPagoReportService $reportService
    ) {}

    // Listar reportes disponibles de la sucursal
    public function index(Branch $branch)
    {
        $reports = $this->reportService->listReports($branch);
        return view('reports.index', compact('branch', 'reports'));
    }

    // Solicitar nuevo reporte
    public function request(Request $request, Branch $branch)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after:from',
        ]);

        $result = $this->reportService->requestReport(
            $branch,
            Carbon::parse($request->from),
            Carbon::parse($request->to),
        );

        // result['status'] = 'pending' — hay que esperar a que sea 'processed'
        return back()->with('info', 'Reporte solicitado. ID: ' . $result['id']);
    }

    // Importar reporte a la BD
    public function import(Branch $branch, string $fileName)
    {
        ImportMpReportJob::dispatch($branch, $fileName);
        return back()->with('success', 'Importación en cola.');
    }
}