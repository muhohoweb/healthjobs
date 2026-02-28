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
        Schema::create('mpesa_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('merchant_request_id')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('account_reference')->nullable();   // package name
            $table->string('mpesa_receipt_number')->nullable();
            $table->integer('result_code')->nullable();
            $table->text('result_desc')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->json('callback_data')->nullable();         // full raw callback stored
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_payments');
    }
};
