<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Field name
            $table->string('label'); // Display label
            $table->string('type'); // string, integer, decimal, boolean, date, datetime, text, json
            $table->string('source_column'); // Database column name
            $table->string('source_table'); // Source table name
            $table->text('description')->nullable();
            $table->boolean('is_sortable')->default(true);
            $table->boolean('is_searchable')->default(true);
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('format')->nullable(); // Display format
            $table->json('validation_rules')->nullable();
            $table->json('filter_options')->nullable(); // Options for filtering
            $table->json('aggregation_functions')->nullable(); // sum, avg, count, etc.
            $table->string('relationship')->nullable(); // belongsTo, hasMany, etc.
            $table->string('related_model')->nullable(); // Related model class
            $table->string('related_field')->nullable(); // Related field name
            $table->timestamps();
            
            $table->index(['report_id', 'is_visible']);
            $table->index(['report_id', 'sort_order']);
            $table->index(['type', 'is_filterable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_fields');
    }
};