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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
    
            $table->string('ticket_number')->unique();
    
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();
    
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();
    
            $table->longText('reason_for_visit');
    
            $table->foreignId('doctor_id')
                ->constrained('users')
                ->cascadeOnDelete();
    
            $table->string('clinic_room_number')->nullable(); // o ->integer('clinic_room_number')
    
            $table->dateTime('appointment_time');
    
            $table->text('notes')->nullable();
    
            $table->string('status')->default('pending');
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
