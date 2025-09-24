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
        Schema::create('patients', function (Blueprint $table) {
            // --- ID y Número de Expediente (sin cambios) ---
            $table->id();
            $table->string('medical_record_number')->unique();

            // --- Relaciones ---
            $table->foreignId('tutor_id')->nullable()->constrained('tutors')->onDelete('set null');
            $table->foreignId('attending_doctor_id')->nullable()->constrained('users')->nullOnDelete();

            // --- Datos Personales (Algunos ahora son Nullable para pre-expedientes) ---
            $table->string('full_name')->nullable(); // Acepta nulos
            $table->date('date_of_birth')->nullable(); // Acepta nulos
            $table->string('sex', 50)->nullable(); // Acepta nulos
            $table->string('curp', 18)->nullable()->unique();
            $table->string('locality')->nullable();
            
            // --- Clasificación y Estado ---
            $table->string('patient_type', 100)->nullable(); // Acepta nulos
            $table->string('status')->default('active'); // Campo para el flujo de la API
            $table->string('employee_status', 100)->nullable();
            $table->string('shift', 50)->nullable();
            $table->string('visit_type', 50)->nullable();
            
            // --- Detalles Adicionales ---
            $table->boolean('has_disability')->default(false);
            $table->text('disability_details')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};