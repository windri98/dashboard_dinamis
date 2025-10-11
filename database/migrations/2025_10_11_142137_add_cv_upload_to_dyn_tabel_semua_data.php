<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dyn_tabel_semua_data', function (Blueprint $table) {
            $table->string('cv_upload')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dyn_tabel_semua_data', function (Blueprint $table) {
            $table->dropColumn('cv_upload');
        });
    }
};
