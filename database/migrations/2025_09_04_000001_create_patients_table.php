<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Usamos Schema::create para CONSTRUIR la tabla desde cero.
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('medical_record_number', 20)->unique();
            $table->string('full_name', 255);
            $table->date('date_of_birth');
            $table->string('sex', 50);
            $table->string('patient_type', 100);
            // Las columnas 'age' y 'service' ya no se crean aquí.
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
}