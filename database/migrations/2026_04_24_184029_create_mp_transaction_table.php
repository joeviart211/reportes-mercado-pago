<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // php artisan make:migration create_mp_transactions_table
public function up(): void
{
    Schema::create('mp_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

        // Datos del reporte MP
        $table->string('operation_id')->index();         // ID DE OPERACIÓN EN MERCADO PAGO
        $table->string('payment_method')->nullable();    // TIPO DE MEDIO DE PAGO
        $table->string('operation_type');                // TIPO DE OPERACIÓN
        $table->decimal('purchase_amount', 12, 2);       // VALOR DE LA COMPRA
        $table->decimal('commission', 12, 2)->default(0);// COMISIONES + IVA
        $table->decimal('net_amount', 12, 2);            // MONTO NETO DE LA OPERACIÓN
        $table->decimal('tax_retention', 12, 2)->default(0); // IMPUESTOS COBRADOS POR RETENCIONES DE IIBB
        $table->string('order_id')->nullable()->index(); // ID DE LA ORDEN
        $table->string('shipment_id')->nullable();       // ID DEL ENVÍO
        $table->string('package_id')->nullable();        // ID DEL PAQUETE
        $table->string('sales_channel')->nullable();     // CANAL DE VENTA
        $table->string('payment_platform')->nullable();  // PLATAFORMA DE COBRO
        $table->timestamp('origin_at')->nullable();      // FECHA DE ORIGEN
        $table->timestamp('approved_at')->nullable();    // FECHA DE APROBACIÓN
        $table->timestamp('released_at')->nullable();    // FECHA DE LIBERACIÓN DEL DINERO

        $table->timestamps();

        // Evitar duplicados al reimportar
        $table->unique(['branch_id', 'operation_id', 'operation_type']);
    });
}
};
