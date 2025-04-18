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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_title')->nullable();
            $table->string('skills')->nullable();
            $table->string('languages')->nullable();
            $table->string('resume')->nullable();
            $table->string('cover_letter')->nullable();
            $table->string('location')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('bio')->nullable();
            $table->text('extra_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
