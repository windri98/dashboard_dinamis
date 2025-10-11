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
        Schema::create('api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('endpoint', 255);
            $table->enum('method', ['GET', 'POST', 'PUT', 'DELETE']);
            $table->string('table_name')->nullable();
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('use_ip_restriction')->default(false);
            $table->text('ip_whitelist')->nullable();
            $table->text('ip_blacklist')->nullable();
            $table->boolean('use_rate_limit')->default(false);
            $table->integer('rate_limit_max')->default(60);
            $table->integer('rate_limit_period')->default(60);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('table_name');
            $table->index('endpoint');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_endpoints');
    }
};
