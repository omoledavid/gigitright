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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); // User reporting the content
            $table->foreignId('reportable_id'); // ID of the reported post or comment
            $table->string('reportable_type'); // Model type: 'Post' or 'Comment'
            $table->string('reason'); // Reason for reporting
            $table->string('status')->default('pending')->comment('pending, reviewed, dismissed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
