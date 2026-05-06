<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpTransaction extends Model
{
    protected $fillable = [
        'branch_id',

        // Identificación
        'operation_id',
        'operation_type',
        'external_reference',

        // Pago
        'payment_method',
        'payment_method_type',
        'installments',

        // Montos
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

        // Impuestos
        'tax_detail',

        // Monedas
        'transaction_currency',
        'settlement_currency',

        // Relación ML
        'order_id',
        'shipment_id',
        'package_id',

        // Canal / POS
        'sales_channel',
        'store_id',
        'pos_id',
        'pos_name',

        // Logística
        'shipment_mode',

        // Metadata
        'metadata',
        'operation_tags',

        // Otros existentes
        'payment_platform',

        // Fechas
        'origin_at',
        'approved_at',
        'released_at',
        'file_name',
    ];

    protected $casts = [
        // Montos
        'purchase_amount' => 'decimal:2',
        'seller_amount'   => 'decimal:2',
        'real_amount'     => 'decimal:2',
        'coupon_amount'   => 'decimal:2',

        'commission'      => 'decimal:2',
        'mkp_fee'         => 'decimal:2',
        'financing_fee'   => 'decimal:2',
        'shipping_fee'    => 'decimal:2',

        'net_amount'      => 'decimal:2',
        'tax_retention'   => 'decimal:2',

        // JSON
        'tax_detail'      => 'array',
        'metadata'        => 'array',

        // Otros
        'installments'    => 'integer',

        // Fechas
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
        return $query->where('operation_type', 'SETTLEMENT');
    }

    public function scopeClaims(Builder $query): Builder
    {
        return $query->where('operation_type', 'DISPUTE');
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
        return $this->operation_type === 'SETTLEMENT';
    }

    public function isClaim(): bool
    {
        return $this->operation_type === 'DISPUTE';
    }
}