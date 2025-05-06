<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pix_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('authkey');
            $table->string('gtkey');
            $table->string('external_transaction_id');
            $table->integer('amount'); // em centavos
            $table->string('status');
            $table->json('pix')->nullable();
            $table->timestamp('created_at_api')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pix_transactions');
    }
};
