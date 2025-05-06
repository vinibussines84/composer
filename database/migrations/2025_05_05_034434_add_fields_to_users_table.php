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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('taxa_cash_in', 5, 2)->nullable();
            $table->decimal('taxa_cash_out', 5, 2)->nullable();
            $table->string('authkey')->nullable();
            $table->string('gtkey')->nullable();
            $table->string('senha')->nullable(); // se for senha de integração, não criptografada
            $table->boolean('cashin_ativo')->default(true);
            $table->boolean('cashout_ativo')->default(true);
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
