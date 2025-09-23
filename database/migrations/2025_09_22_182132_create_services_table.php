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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('cost', 8, 2)->default(0.00);
            $table->string('department')->nullable();

            // --- CAMPOS AÑADIDOS ---

            // Campo de texto flexible para el horario. Ej: "Lunes a Viernes, 8:00 - 14:00"
            $table->string('schedule')->nullable();

            // Campo para el turno. Usaremos el Enum que ya creamos para los pacientes.
            $table->string('shift')->nullable();

            // Vínculo con el usuario responsable del servicio.
            // Si el usuario se elimina, este campo se pondrá en nulo.
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
