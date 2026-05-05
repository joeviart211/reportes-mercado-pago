<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mp_transactions', function (Blueprint $table) {
           $table->string('external_reference')->nullable()->after('operation_type');

            // ─── Pago ──────────────────────────────────
            $table->string('payment_method_type')->nullable()->after('payment_method');
            $table->integer('installments')->default(0)->after('payment_method_type');

            // ─── Montos adicionales ────────────────────
            $table->decimal('seller_amount', 12, 2)->default(0)->after('purchase_amount');
            $table->decimal('real_amount', 12, 2)->default(0)->after('seller_amount');
            $table->decimal('coupon_amount', 12, 2)->default(0)->after('real_amount');

            // ─── Desglose de comisiones ────────────────
            $table->decimal('mkp_fee', 12, 2)->default(0)->after('commission');
            $table->decimal('financing_fee', 12, 2)->default(0)->after('mkp_fee');
            $table->decimal('shipping_fee', 12, 2)->default(0)->after('financing_fee');

            // ─── Impuestos detallados ──────────────────
            $table->json('tax_detail')->nullable()->after('tax_retention');

            // ─── Monedas ───────────────────────────────
            $table->string('transaction_currency', 10)->nullable()->after('net_amount');
            $table->string('settlement_currency', 10)->nullable()->after('transaction_currency');

            // ─── POS / tienda ──────────────────────────
            $table->string('store_id')->nullable()->after('sales_channel');
            $table->string('pos_id')->nullable()->after('store_id');
            $table->string('pos_name')->nullable()->after('pos_id');

            // ─── Logística ─────────────────────────────
            $table->string('shipment_mode')->nullable()->after('shipment_id');

            // ─── Metadata ──────────────────────────────
            $table->json('metadata')->nullable()->after('shipment_mode');
            $table->string('operation_tags')->nullable()->after('metadata');       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mp_transactions', function (Blueprint $table) {
              $table->dropColumn([
                'external_reference',
                'payment_method_type',
                'installments',
                'seller_amount',
                'real_amount',
                'coupon_amount',
                'mkp_fee',
                'financing_fee',
                'shipping_fee',
                'tax_detail',
                'transaction_currency',
                'settlement_currency',
                'store_id',
                'pos_id',
                'pos_name',
                'shipment_mode',
                'metadata',
                'operation_tags',
            ]);
        });
    }
};
