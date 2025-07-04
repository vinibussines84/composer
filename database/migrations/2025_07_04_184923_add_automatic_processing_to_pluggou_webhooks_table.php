<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pluggou_webhooks', function (Blueprint $table) {
            $table->boolean('automatic_processing')->default(false);
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pluggou_webhooks', function (Blueprint $table) {
            //
        });
    }
};
