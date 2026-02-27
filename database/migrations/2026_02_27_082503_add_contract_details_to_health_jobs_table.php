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
        Schema::table('health_jobs', function (Blueprint $table) {
            $table->string('contract_duration')->nullable()->after('job_type');
            $table->json('responsibilities')->nullable()->after('description');
            $table->date('deadline')->nullable()->after('requirements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_jobs', function (Blueprint $table) {
            $table->dropColumn(['contract_duration', 'responsibilities', 'deadline']);
        });
    }
};
