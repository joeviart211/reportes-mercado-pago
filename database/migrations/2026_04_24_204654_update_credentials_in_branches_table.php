<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // php artisan make:migration update_credentials_in_branches_table

public function up(): void
{
    Schema::table('branches', function (Blueprint $table) {
        $table->string('ml_client_id')
              ->nullable()
              ->after('slug');

        $table->renameColumn('ml_api_key', 'ml_client_secret');
    });
}

public function down(): void
{
    Schema::table('branches', function (Blueprint $table) {
        $table->dropColumn('ml_client_id');
        $table->renameColumn('ml_client_secret', 'ml_api_key');
    });
}
};
