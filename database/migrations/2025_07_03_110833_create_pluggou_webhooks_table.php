<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pluggou_webhooks', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pluggou_webhooks');
    }
};
