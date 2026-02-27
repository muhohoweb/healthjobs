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
            $table->string('contact_phone')->nullable()->after('deadline');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->text('how_to_apply')->nullable()->after('contact_email');
            $table->string('contact_address')->nullable()->after('how_to_apply');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_jobs', function (Blueprint $table) {
            $table->dropColumn(['contract_duration', 'responsibilities', 'deadline','contact_phone', 'contact_email', 'how_to_apply', 'contact_address']);
        });
    }
};
