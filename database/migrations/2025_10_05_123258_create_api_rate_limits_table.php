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
        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_endpoint_id')
                ->constrained('api_endpoints')
                ->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->integer('request_count')->default(1);
            $table->timestamp('window_start')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // unique key api_endpoint_id + ip_address
            $table->unique(['api_endpoint_id', 'ip_address'], 'unique_endpoint_ip');

            // index tambahan
            $table->index('window_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_rate_limits');
    }
};
