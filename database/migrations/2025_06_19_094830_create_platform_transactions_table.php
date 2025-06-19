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
        Schema::create('platform_transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->string('source'); // job_upload, job_application, gig
            $table->enum('type', ['charge', 'refund']);
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('completed');
            $table->morphs('model'); // adds model_type + model_id
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_transactions');
    }
};
