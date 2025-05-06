<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pix_transactions', function (Blueprint $table) {
            $table->tinyInteger('balance_type')->default(1)->after('amount'); // 1 = Entrada, 0 = Saída
        });
    }

    public function down(): void
    {
        Schema::table('pix_transactions', function (Blueprint $table) {
            $table->dropColumn('balance_type');
        });
    }
};
