<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('queue_slot_id')->nullable()->constrained('queue_slots')->onDelete('set null');
            $table->integer('queue_number');
            $table->dateTime('scheduled_at');
            $table->enum('status', ['waiting_payment', 'confirmed', 'called', 'in_progress', 'completed', 'no_show', 'cancelled'])->default('waiting_payment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
