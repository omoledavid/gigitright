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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('budget')->nullable();
            $table->date('deadline')->nullable();
            $table->string('skills')->nullable();
            $table->string('category_id')->nullable();
            $table->string('sub_category_id')->nullable();
            $table->string('duration')->nullable();
            $table->text('skill_requirements')->nullable();
            $table->text('attachments')->nullable();
            $table->string('location')->nullable();
            $table->string('job_type')->nullable()->comment('fixed, hourly, monthly');
            $table->integer('visibility')->default(0)->comment('private, public');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
