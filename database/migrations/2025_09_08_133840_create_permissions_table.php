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
            $table->string('dyn_menu_id')->constrained()->onDelete('cascade');
            $table->string('dyn_menu_item_id')->constrained()->onDelete('cascade');
            $table->string('action_id')->contrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique([
                'dyn_menu_id', 
                'dyn_menu_item_id', 
                'action_id'
            ]);
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
