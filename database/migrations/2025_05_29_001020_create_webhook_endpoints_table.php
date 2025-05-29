<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  // database/migrations/xxxx_xx_xx_create_webhook_endpoints_table.php
public function up()
{
    Schema::create('webhook_endpoints', function (Blueprint $table) {
        $table->id();
        $table->string('url');
        $table->string('token')->nullable(); // Para autenticação opcional
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
    }
};
