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
        // Instrucciones para modificar la tabla 'patients'
        Schema::table('patients', function (Blueprint $table) {
            // Hacemos que estas columnas acepten valores nulos (nullable)
            // El método change() modifica una columna existente.
            $table->string('full_name')->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->string('sex', 50)->nullable()->change();
            $table->string('patient_type', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Instrucciones para revertir los cambios
        Schema::table('patients', function (Blueprint $table) {
            // Volvemos a hacer que las columnas sean obligatorias (no nulas)
            $table->string('full_name')->nullable(false)->change();
            $table->date('date_of_birth')->nullable(false)->change();
            $table->string('sex', 50)->nullable(false)->change();
            $table->string('patient_type', 100)->nullable(false)->change();
        });
    }
};