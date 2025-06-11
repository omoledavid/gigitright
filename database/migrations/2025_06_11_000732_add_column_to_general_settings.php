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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('email_from');
            $table->string('alt_phone_number')->nullable()->after('phone_number');
            $table->string('address')->nullable()->after('alt_phone_number');
            $table->string('site_description')->nullable()->after('address');
            $table->string('site_keywords')->nullable()->after('site_description');
            $table->string('logo')->nullable()->after('site_keywords');
            $table->string('dark_logo')->nullable()->after('logo');
            $table->string('favicon')->nullable()->after('dark_logo');
            $table->string('facebook')->nullable()->after('favicon');
            $table->string('twitter')->nullable()->after('facebook');
            $table->string('instagram')->nullable()->after('twitter');
            $table->string('linkedin')->nullable()->after('instagram');
            $table->string('youtube')->nullable()->after('linkedin');
            $table->boolean('maintenance_mode')->default(false)->after('youtube');
            $table->boolean('login_status')->default(true)->after('maintenance_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            //
        });
    }
};
