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
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->unsignedInteger('queue_number');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->dateTime('estimated_service_time')->nullable()->comment('Bisa diisi dengan kalkulasi booking_date + booking_time');
            $table->enum('status', ['pending_payment', 'confirmed', 'called', 'completed', 'no_show', 'cancelled'])->default('pending_payment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
