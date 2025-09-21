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
        Schema::create('table_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dynamic_table_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('column_name');
            $table->enum('type', ['string', 'text', 'integer', 'decimal', 'date','time', 'datetime', 'boolean', 'enum']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(true);
            $table->boolean('is_sortable')->default(true);
            $table->boolean('show_in_list')->default(true);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['dynamic_table_id', 'is_active', 'order']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_columns');
    }
};
