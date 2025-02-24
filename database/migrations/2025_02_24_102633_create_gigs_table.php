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
        Schema::create('gigs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('description');
            $table->json('skills')->nullable(); // Storing an array of skills
            $table->string('location')->nullable();
            $table->json('previous_works_companies')->nullable(); // Array of previous companies
            $table->string('language')->nullable();
            $table->text('unique_selling_point')->nullable(); // What makes you different
            $table->json('plans')->nullable(); // Storing multiple plans with details
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gigs');
    }
};
