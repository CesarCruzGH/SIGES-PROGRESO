<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration
{
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('medical_record_number', 20)->unique();
            $table->string('full_name', 255);
            $table->date('date_of_birth');
            $table->string('sex', 50);
            $table->string('patient_type', 100);
            $table->string('service', 150);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
}
