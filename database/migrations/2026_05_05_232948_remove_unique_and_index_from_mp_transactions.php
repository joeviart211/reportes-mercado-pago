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
            $table->dropUnique([ 'operation_id', 'operation_type']);
            $table->dropIndex(['operation_id']);
        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mp_transactions', function (Blueprint $table) {
            $table->unique(['operation_id', 'operation_type']);
            $table->index('operation_id');
        });
    }
};
