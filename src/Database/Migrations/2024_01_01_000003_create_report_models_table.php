<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_models', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Human readable name
            $table->string('model_class'); // Full model class name
            $table->string('table_name'); // Database table name
            $table->string('primary_key')->default('id'); // Primary key column
            $table->text('description')->nullable();
            $table->json('available_fields')->nullable(); // Available fields from model
            $table->json('relationships')->nullable(); // Available relationships
            $table->json('scopes')->nullable(); // Available query scopes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('model_class');
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_models');
    }
};