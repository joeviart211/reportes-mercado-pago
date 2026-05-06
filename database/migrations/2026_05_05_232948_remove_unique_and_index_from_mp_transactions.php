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

            // 1. quitar FK
            $table->dropForeign(['branch_id']);

            // 2. quitar UNIQUE
            $table->dropUnique('mp_transactions_branch_id_operation_id_operation_type_unique');

            // 3. crear índice normal (opcional pero recomendado)
            $table->index(['branch_id', 'operation_id', 'operation_type']);

            // 4. volver a crear FK
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');
        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mp_transactions', function (Blueprint $table) {
            $table->unique(['operation_type']);
        });
    }
};
