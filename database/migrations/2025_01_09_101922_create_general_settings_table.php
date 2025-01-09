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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('cur_text')->nullable();
            $table->string('cur_sym')->nullable();
            $table->string('email_form')->nullable();
            $table->string('email_template')->nullable();
            $table->string('mail_config')->nullable();
            $table->string('global_shortcodes')->nullable();
            $table->integer('kv')->default(0);
            $table->integer('ev')->default(0);
            $table->integer('sm')->default(0);
            $table->integer('register_status')->default(1);
            $table->integer('deposit_status')->default(0);
            $table->integer('withdraw_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
