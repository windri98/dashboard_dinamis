<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix any string created_at values to proper timestamp format
        try {
            DB::statement("UPDATE api_access_logs SET created_at = NOW() WHERE created_at IS NULL OR created_at = ''");
        } catch (\Exception $e) {
            // Table might not exist yet, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
