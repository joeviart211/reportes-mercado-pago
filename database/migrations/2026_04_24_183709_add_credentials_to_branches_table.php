<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // php artisan make:migration add_credentials_to_branches_table
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Mercado Libre OAuth
            $table->text('ml_access_token')->nullable()->after('ml_api_key');
            $table->text('ml_refresh_token')->nullable()->after('ml_access_token');
            $table->timestamp('ml_token_expires_at')->nullable()->after('ml_refresh_token');
            $table->string('ml_user_id')->nullable()->after('ml_token_expires_at');

            // Mercado Pago (puede ser la misma app o distinta)
            $table->text('mp_access_token')->nullable()->after('ml_user_id');
            $table->text('mp_refresh_token')->nullable()->after('mp_access_token');
            $table->timestamp('mp_token_expires_at')->nullable()->after('mp_refresh_token');
            $table->string('mp_user_id')->nullable()->after('mp_token_expires_at');
            
            $table->boolean('active')->default(true)->after('mp_user_id');
        });
    }
};
