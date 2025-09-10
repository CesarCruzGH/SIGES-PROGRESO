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
        // Usamos Schema::table para MODIFICAR la tabla que ya existe.
        Schema::table('patients', function (Blueprint $table) {
            // Esta migración AÑADE las nuevas columnas y quita las viejas.
            // (Aquí va todo el código que te di en la respuesta anterior para este archivo)
            // ...
           // $table->dropColumn('age'); // Esto ya no es necesario si no la creaste en el paso 1, pero no hace daño dejarlo.
            //$table->dropColumn('service'); // Igual que arriba.

            $table->foreignId('tutor_id')->nullable()->after('id')->constrained('tutors')->onDelete('set null');
            $table->foreignId('attending_doctor_id')->nullable()->after('tutor_id')->constrained('users')->onDelete('set null');
            $table->string('curp', 18)->unique()->nullable()->after('date_of_birth');
            $table->string('employee_status', 100)->nullable()->after('patient_type');
            $table->string('shift', 50)->nullable()->after('employee_status');
            $table->string('visit_type', 50)->nullable()->after('shift');
            $table->boolean('has_disability')->default(false)->after('visit_type');
            $table->text('disability_details')->nullable()->after('has_disability');
            $table->string('locality', 255)->nullable()->after('disability_details');
            $table->index('tutor_id');
            $table->index('attending_doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // El método down para revertir los cambios...
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['tutor_id']);
            $table->dropIndex(['attending_doctor_id']);
            $table->dropForeign(['tutor_id']);
            $table->dropColumn('tutor_id');
            $table->dropForeign(['attending_doctor_id']);
            $table->dropColumn('attending_doctor_id');
            $table->dropColumn(['curp', 'employee_status', 'shift', 'visit_type', 'has_disability', 'disability_details', 'locality']);
            $table->string('service', 150)->nullable();
        });
    }
};
