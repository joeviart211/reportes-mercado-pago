<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpTransaction extends Model
{
    protected $fillable = [
        'branch_id',
        'operation_id',
        'payment_method',
        'operation_type',
        'purchase_amount',
        'commission',
        'net_amount',
        'tax_retention',
        'order_id',
        'shipment_id',
        'package_id',
        'sales_channel',
        'payment_platform',
        'origin_at',
        'approved_at',
        'released_at',
    ];

    protected $casts = [
        'purchase_amount' => 'decimal:2',
        'commission'      => 'decimal:2',
        'net_amount'      => 'decimal:2',
        'tax_retention'   => 'decimal:2',
        'origin_at'       => 'datetime',
        'approved_at'     => 'datetime',
        'released_at'     => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('operation_type', 'Pago aprobado');
    }

    public function scopeClaims(Builder $query): Builder
    {
        return $query->where('operation_type', 'Reclamo');
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('origin_at', [$from, $to]);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function isApproved(): bool
    {
        return $this->operation_type === 'Pago aprobado';
    }

    public function isClaim(): bool
    {
        return $this->operation_type === 'Reclamo';
    }
}