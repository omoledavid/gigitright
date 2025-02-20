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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User requesting withdrawal
            $table->foreignId('account_detail_id')->constrained()->onDelete('cascade'); // Bank account details
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // Status: pending, approved, rejected
            $table->text('admin_note')->nullable(); // Admin remarks (if rejected)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraws');
    }
};
