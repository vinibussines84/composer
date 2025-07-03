<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pluggou_webhooks', function (Blueprint $table) {
            $table->id();
            $table->json('payload');          // payload bruto
            $table->string('status')
                  ->default('pending');       // pending | processed | error
            $table->timestamps();             // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pluggou_webhooks');
    }
};
