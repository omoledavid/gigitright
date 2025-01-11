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
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links to users table
            $table->string('degree'); // Degree or qualification (e.g., B.Sc.)
            $table->string('field_of_study')->nullable(); // Major or field of study
            $table->string('institution_name'); // Name of the institution
            $table->date('start_date'); // Start date of the program
            $table->date('end_date')->nullable(); // End date or graduation date
            $table->string('location')->nullable(); // Location of the institution
            $table->string('grade', 100)->nullable(); // Grade or classification
            $table->text('description')->nullable(); // Additional details about the program
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
