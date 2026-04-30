<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Http;

class MercadoLibreAuthService
{
    const AUTH_URL  = 'https://auth.mercadolibre.com.mx/authorization';
    const TOKEN_URL = 'https://api.mercadopago.com/oauth/token';

    // ─── URL de autorización usando las llaves de LA sucursal ─────
    public function getAuthorizationUrl(Branch $branch): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => $branch->ml_client_id,       // ← de la BD
            'redirect_uri'  => route('ml.callback'),
            'state'         => 'branch_' . $branch->id,
        ]);
    }

    // ─── Intercambiar code por tokens usando llaves de LA sucursal ─
    public function exchangeCodeForTokens(Branch $branch, string $code): array
    {
        return Http::asForm()
            ->post(self::TOKEN_URL, [
                'grant_type'    => 'authorization_code',
                'client_id'     => $branch->ml_client_id,     // ← de la BD
                'client_secret' => $branch->ml_client_secret, // ← de la BD
                'code'          => $code,
                'redirect_uri'  => route('ml.callback'),
            ])
            ->throw()
            ->json();
    }

    // ─── Renovar tokens usando las llaves de LA sucursal ──────────
    public function refreshTokens(Branch $branch): void
    {
        $response = Http::asForm()
            ->post(self::TOKEN_URL, [
                'grant_type'    => 'refresh_token',
                'client_id'     => $branch->ml_client_id,     // ← de la BD
                'client_secret' => $branch->ml_client_secret, // ← de la BD
                'refresh_token' => $branch->ml_refresh_token, // ya se descifra solo por el cast
            ])
            ->throw()
            ->json();

        $this->storeTokens($branch, $response);
    }

    // ─── Guardar tokens en la sucursal ────────────────────────────
    public function storeTokens(Branch $branch, array $tokens): void
    {
        $branch->update([
            'ml_access_token'     => $tokens['access_token'],   
            'ml_refresh_token'    => $tokens['refresh_token'],
            'ml_token_expires_at' => now()->addSeconds($tokens['expires_in']),
            'ml_user_id'          => $tokens['user_id'],
        ]);
    }

    // ─── Obtener token válido para una sucursal ───────────────────
    public function getValidToken(Branch $branch): string
    {
        if ($branch->mlTokenIsExpired()) {
            $this->refreshTokens($branch);
            $branch->refresh();
        }

        return $branch->ml_access_token; // cast 'encrypted' lo descifra solo
    }
}