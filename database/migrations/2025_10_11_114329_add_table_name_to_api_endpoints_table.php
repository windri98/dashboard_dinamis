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
        Schema::table('api_endpoints', function (Blueprint $table) {
            $table->string('table_name')->nullable()->after('method');
            $table->index('table_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_endpoints', function (Blueprint $table) {
            $table->dropIndex(['table_name']);
            $table->dropColumn('table_name');
        });
    }
};
