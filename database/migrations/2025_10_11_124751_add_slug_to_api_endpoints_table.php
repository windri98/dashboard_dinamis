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
        Schema::table('api_endpoints', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Generate slug untuk data existing
        $endpoints = \App\Models\ApiEndpoint::all();
        foreach ($endpoints as $endpoint) {
            $endpoint->slug = \App\Models\ApiEndpoint::generateSlug($endpoint->name, $endpoint->id);
            $endpoint->save();
        }

        // Sekarang baru buat unique constraint
        Schema::table('api_endpoints', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_endpoints', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
