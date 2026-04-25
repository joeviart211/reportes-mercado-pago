<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Verifica si el usuario pertenece a una sucursal activa.
     */
    public function hasActiveBranch(): bool
    {
        return $this->branch !== null && $this->branch->active;
    }

    /**
     * Verifica si el usuario es superadmin (sin sucursal asignada).
     */
    public function isSuperAdmin(): bool
    {
        return is_null($this->branch_id);
    }
}