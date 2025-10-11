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
        Schema::create('api_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_endpoint_id')
                ->constrained('api_endpoints')
                ->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('request_method', 10);
            $table->string('request_uri', 500);
            $table->text('request_payload')->nullable();
            $table->integer('response_status')->nullable();
            $table->text('response_message')->nullable();
            $table->boolean('access_granted')->default(true);
            $table->string('block_reason', 255)->nullable();
            $table->float('execution_time')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // index tambahan sesuai SQL asli
            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_access_logs');
    }
};
