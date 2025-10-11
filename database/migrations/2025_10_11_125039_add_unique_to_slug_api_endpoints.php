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
            // Cek apakah unique constraint sudah ada
            $indexExists = collect(DB::select("SHOW INDEX FROM api_endpoints WHERE Key_name = 'api_endpoints_slug_unique'"))->isNotEmpty();
            
            if (!$indexExists) {
                $table->unique('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_endpoints', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['slug']);
        });
    }
};
