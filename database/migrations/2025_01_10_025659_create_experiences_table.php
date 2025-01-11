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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links to users table
            $table->string('job_title'); // Job or role title
            $table->string('company_name'); // Name of the company or organization
            $table->date('start_date'); // Start date of the experience
            $table->date('end_date')->nullable(); // End date, nullable for ongoing roles
            $table->string('location')->nullable(); // Location of the job
            $table->text('description')->nullable(); // Responsibilities or additional details
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
