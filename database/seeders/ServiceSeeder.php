<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Consulta General',
                'description' => 'Atención médica general para diagnóstico y tratamiento de enfermedades comunes.',
                'is_active' => true,
            ],
            [
                'name' => 'Pediatría',
                'description' => 'Atención médica especializada para niños y adolescentes.',
                'is_active' => true,
            ],
            [
                'name' => 'Ginecología',
                'description' => 'Atención médica especializada en salud femenina y sistema reproductivo.',
                'is_active' => true,
            ],
            [
                'name' => 'Cardiología',
                'description' => 'Diagnóstico y tratamiento de enfermedades del corazón y sistema cardiovascular.',
                'is_active' => true,
            ],
            [
                'name' => 'Traumatología',
                'description' => 'Atención especializada en lesiones y enfermedades del sistema músculo-esquelético.',
                'is_active' => true,
            ],
            [
                'name' => 'Dermatología',
                'description' => 'Diagnóstico y tratamiento de enfermedades de la piel, cabello y uñas.',
                'is_active' => true,
            ],
            [
                'name' => 'Oftalmología',
                'description' => 'Atención especializada en salud visual y enfermedades de los ojos.',
                'is_active' => true,
            ],
            [
                'name' => 'Odontología',
                'description' => 'Servicios de salud bucal, incluyendo prevención, diagnóstico y tratamiento.',
                'is_active' => true,
            ],
            [
                'name' => 'Nutrición',
                'description' => 'Asesoramiento nutricional y planes de alimentación personalizados.',
                'is_active' => true,
            ],
            [
                'name' => 'Psicología',
                'description' => 'Atención en salud mental y apoyo psicológico.',
                'is_active' => true,
            ],
            [
                'name' => 'Fisioterapia',
                'description' => 'Rehabilitación física y tratamiento de lesiones musculares y articulares.',
                'is_active' => true,
            ],
            [
                'name' => 'Laboratorio Clínico',
                'description' => 'Análisis de muestras biológicas para diagnóstico y seguimiento de enfermedades.',
                'is_active' => true,
            ],
            [
                'name' => 'Radiología',
                'description' => 'Estudios de imagen para diagnóstico médico.',
                'is_active' => true,
            ],
            [
                'name' => 'Urgencias',
                'description' => 'Atención médica inmediata para situaciones que requieren intervención rápida.',
                'is_active' => true,
            ],
            [
                'name' => 'Vacunación',
                'description' => 'Administración de vacunas para prevención de enfermedades.',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}