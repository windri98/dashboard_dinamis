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
        // Update the type enum to include file and image types
        DB::statement("ALTER TABLE table_columns MODIFY COLUMN type ENUM('string', 'text', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'select', 'radio', 'checkbox', 'file', 'image') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE table_columns MODIFY COLUMN type ENUM('string', 'text', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'select', 'radio', 'checkbox') NOT NULL");
    }
};
