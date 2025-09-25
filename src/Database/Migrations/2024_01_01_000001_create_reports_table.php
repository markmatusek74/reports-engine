<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->string('type')->default('table'); // table, chart, dashboard
            $table->json('configuration')->nullable(); // Report configuration data
            $table->json('filters')->nullable(); // Default filters
            $table->json('sorting')->nullable(); // Default sorting
            $table->json('columns')->nullable(); // Column definitions
            $table->boolean('is_public')->default(false);
            $table->boolean('is_cached')->default(true);
            $table->integer('cache_ttl')->default(3600); // Cache time in seconds
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'is_public']);
            $table->index(['created_by']);
            $table->index(['slug']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};