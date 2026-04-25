<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'slug',

        // Credenciales de la app ML de esta sucursal
        'ml_client_id',
        'ml_client_secret',   // era ml_api_key — renombrar en migración

        // Tokens OAuth ML/MP (mismo token sirve para ambos)
        'ml_access_token',
        'ml_refresh_token',
        'ml_token_expires_at',
        'ml_user_id',

        // Solo si usas una app de MP separada por sucursal
        'mp_access_token',
        'mp_refresh_token',
        'mp_token_expires_at',
        'mp_user_id',

        'active',
    ];

    protected $hidden = [
        'ml_client_secret',
        'ml_access_token',
        'ml_refresh_token',
        'mp_access_token',
        'mp_refresh_token',
    ];

    protected $casts = [
        // Campos sensibles — Laravel cifra/descifra automáticamente
        'ml_client_secret'    => 'encrypted',
        'ml_access_token'     => 'encrypted',
        'ml_refresh_token'    => 'encrypted',
        'mp_access_token'     => 'encrypted',
        'mp_refresh_token'    => 'encrypted',

        'ml_token_expires_at' => 'datetime',
        'mp_token_expires_at' => 'datetime',
        'active'              => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MpTransaction::class);
    }

    // ─── Helpers de tokens ────────────────────────────────────────

    public function mlTokenIsExpired(): bool
    {
        return ! $this->ml_token_expires_at || now()->gte($this->ml_token_expires_at);
    }

    public function mpTokenIsExpired(): bool
    {
        return ! $this->mp_token_expires_at || now()->gte($this->mp_token_expires_at);
    }

    public function isConnectedToMl(): bool
    {
        return filled($this->ml_client_id)
            && filled($this->ml_client_secret)
            && filled($this->ml_access_token);
    }

    public function isConnectedToMp(): bool
    {
        // Si no tiene app MP separada, reutiliza el token ML
        return filled($this->mp_access_token) || $this->isConnectedToMl();
    }

    // Token activo para llamadas a MP (prioriza mp si existe, si no usa ml)
    public function getActiveMpToken(): ?string
    {
        return filled($this->mp_access_token)
            ? $this->mp_access_token
            : $this->ml_access_token;
    }

    // ─── Route model binding ──────────────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}