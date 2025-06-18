<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bloobank_webhooks', function (Blueprint $table) {
            $table->longText('payload')->nullable()->after('id');
            $table->string('status')->default('pending')->after('payload');
        });
    }

    public function down(): void
    {
        Schema::table('bloobank_webhooks', function (Blueprint $table) {
            $table->dropColumn(['payload', 'status']);
        });
    }
};
