<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('cashin_ativo')->default(1)->change();
            $table->tinyInteger('cashout_ativo')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('cashin_ativo')->default(true)->change();
            $table->boolean('cashout_ativo')->default(true)->change();
        });
    }
};
