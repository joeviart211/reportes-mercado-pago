<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\MercadoLibreAuthService;
use Illuminate\Http\Request;

class MlAuthController extends Controller
{
    public function __construct(
        protected MercadoLibreAuthService $authService
    ) {}

    public function redirect(Branch $branch)
    {
        return redirect($this->authService->getAuthorizationUrl($branch));
    }

    public function callback(Request $request)
    {
        abort_unless($request->filled('code') && $request->filled('state'), 400);

        $branchId = (int) str_replace('branch_', '', $request->state);
        $branch   = Branch::findOrFail($branchId);

        $tokens = $this->authService->exchangeCodeForTokens($branch, $request->code);
        $this->authService->storeTokens($branch, $tokens);

        return redirect()
            ->route('branches.reports.index', $branch)
            ->with('success', 'Mercado Libre conectado.');
    }
}