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
        Schema::create('dynamic_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dynamic_menu_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->default('fas fa-link');
            $table->enum('link_type', ['table', 'route', 'url'])->default('table');
            $table->string('link_value')->nullable();
            $table->string('permission_key')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['dynamic_menu_id', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_menu_items');
    }
};
