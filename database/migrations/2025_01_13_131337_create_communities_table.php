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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique community name
            $table->string('slug')->unique(); // SEO-friendly identifier
            $table->text('description')->nullable(); // Optional description
            $table->unsignedBigInteger('created_by'); // User who created the community
            $table->string('cover_image')->nullable(); // Cover image for the community
            $table->boolean('is_private')->default(false); // Public or private community
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
