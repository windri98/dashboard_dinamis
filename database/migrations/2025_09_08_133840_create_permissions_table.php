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
            $table->string('menu_id')->constrained()->onDelete('cascade');
            $table->string('menu_item_id')->constrained()->onDelete('cascade');
            $table->string('action_id')->contrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique([
                'menu_id', 
                'menu_item_id', 
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
