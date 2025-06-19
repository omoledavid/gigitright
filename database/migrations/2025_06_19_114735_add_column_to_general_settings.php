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
            $table->integer('job_charge')->default(1)->after('sm')->comment('Job charge in percentage');
            $table->integer('gig_charge')->default(1)->after('job_charge')->comment('Gig charge in percentage');
            $table->integer('job_apply_charge')->default(1)->after('gig_charge')->comment('Job application charge in gft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['job_charge', 'gig_charge', 'job_apply_charge']);
        });
    }
};
