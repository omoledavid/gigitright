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
        Schema::table('milestones', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_paid')->default(false)->after('status');
            $table->boolean('is_marked_complete_by_talent')->default(false)->after('is_paid');
            $table->boolean('is_marked_complete_by_client')->default(false)->after('is_marked_complete_by_talent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn('is_paid', 'is_marked_complete_by_talent', 'is_marked_complete_by_client');
        });
    }
};
