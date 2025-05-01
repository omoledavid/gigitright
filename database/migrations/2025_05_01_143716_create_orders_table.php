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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id');
            $table->foreignId('talent_id');
            $table->foreignId('gig_id')->constrained('gigs')->onDelete('cascade');
            $table->string('plan_name')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'delivered', 'completed', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->boolean('client_mark_as_complete')->default(false);
            $table->boolean('talent_mark_as_complete')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
