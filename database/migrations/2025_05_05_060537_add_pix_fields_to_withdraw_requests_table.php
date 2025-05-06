<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('withdraw_requests', 'pix_type')) {
                $table->string('pix_type')->after('user_id');
            }

            if (!Schema::hasColumn('withdraw_requests', 'pix_key')) {
                $table->string('pix_key')->after('pix_type');
            }

            if (!Schema::hasColumn('withdraw_requests', 'amount')) {
                $table->integer('amount')->after('pix_key'); // em centavos
            }

            if (!Schema::hasColumn('withdraw_requests', 'status')) {
                $table->string('status')->default('pending')->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->dropColumn(['pix_type', 'pix_key', 'amount']);
            // status deixado de fora, pois já existe originalmente
        });
    }
};
