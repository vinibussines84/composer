<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pix_transactions', function (Blueprint $table) {
            $table->string('end_to_end_id')->nullable()->after('external_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('pix_transactions', function (Blueprint $table) {
            $table->dropColumn('end_to_end_id');
        });
    }
};
