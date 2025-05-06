<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'taxa_cash_in')) {
                $table->decimal('taxa_cash_in', 5, 2)->default(0)->after('email');
            }

            if (!Schema::hasColumn('users', 'taxa_cash_out')) {
                $table->decimal('taxa_cash_out', 5, 2)->default(0)->after('taxa_cash_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'taxa_cash_in')) {
                $table->dropColumn('taxa_cash_in');
            }

            if (Schema::hasColumn('users', 'taxa_cash_out')) {
                $table->dropColumn('taxa_cash_out');
            }
        });
    }
};
