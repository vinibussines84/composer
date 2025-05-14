<?php

// database/migrations/xxxx_xx_xx_add_provedora_to_pix_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pix_transactions', function (Blueprint $table) {
            $table->string('provedora')->nullable()->after('gtkey');
        });
    }

    public function down(): void
    {
        Schema::table('pix_transactions', function (Blueprint $table) {
            $table->dropColumn('provedora');
        });
    }
};
