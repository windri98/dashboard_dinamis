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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('dynamic_menus')->onDelete('cascade');
            $table->foreignId('menu_item_id')->nullable()->constrained('dynamic_menu_items');
            $table->foreignId('action_id')->nullable()->constrained('actions');
            $table->timestamps();
            
            $table->index(['menu_id', 'menu_item_id', 'action_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
