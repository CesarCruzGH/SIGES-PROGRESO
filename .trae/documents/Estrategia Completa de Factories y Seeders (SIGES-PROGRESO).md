## Objetivos
- Poblar toda la base con datos coherentes y relaciones reales.
- Respetar la automatización: al crear `Patient`, se crea `MedicalRecord` automáticamente (no crear `MedicalRecord` en factories/seeders).
- Generar turnos/consultorios, servicios, usuarios (roles), pacientes y visitas con estados variados; incluir somatometría, notas de enfermería, documentos y recetas.

## Orden de Poblado
1. Usuarios (`User`) con roles: administrador, recepcionista, médicos.
2. Servicios (`Service`) con responsable.
3. Consultorios y turnos (`ClinicSchedule`): días recientes (ayer, hoy, mañana), distintos turnos; algunos abiertos.
4. Pacientes (`Patient`): al crearse, se generan sus `MedicalRecord` automáticamente.
5. Visitas (`Appointment`): distribuidas por estados (pending, in_progress, completed, cancelled), enlazadas a consultorios abiertos.
6. Recetas (`Prescription`) para visitas completadas (proporción configurable), con items.
7. Somatometría (`SomatometricReading`) para visitas en progreso/completadas.
8. Evaluación de enfermería (`NursingAssessment`) y documentos médicos (`MedicalDocument`) opcionales.

## Factories Propuestos
- `database/factories/UserFactory.php`
  - Campos: `name`, `email`, `password`, `role` (`App\Enums\UserRole`).
  - States: `doctor()`, `receptionist()`, `admin()`.
- `database/factories/ServiceFactory.php`
  - `name`, `description`, `cost`, `department`, `is_active`, `shift` (enum), `responsible_id` (usuario con rol director/médico).
  - Referencia: modelo en `app/Models/Service.php:14–37`.
- `database/factories/ClinicScheduleFactory.php`
  - `clinic_name`, `user_id` (doctor), `service_id`, `shift` (enum), `date` (cercana), `is_active`=true, `is_shift_open` aleatorio.
  - Si `is_shift_open`=true: set `opened_by`, `shift_opened_at`.
  - Referencia: `app/Models/ClinicSchedule.php:14–37`.
- `database/factories/PatientFactory.php`
  - `full_name`, `date_of_birth`, `sex ('M'/'F')`, `curp` opcional, `locality` (enum), `contact_phone`, `address`, `has_disability`/`disability_details`, `status` ('active'/'pending_review').
  - Al crear `Patient`, se auto-crea `MedicalRecord` (evento): `app/Models/Patient.php:60–66`.
- `database/factories/AppointmentFactory.php`
  - Derivar de un `Patient` existente: `medical_record_id = $patient->medicalRecord->id`.
  - `clinic_schedule_id`: tomar uno abierto y consistente con `date` y `shift`, setear `service_id` y `doctor_id` desde el schedule.
  - `ticket_number`: permitir vacío para generar walk-in en `creating()` del modelo (ver `app/Models/Appointment.php:42–47, 69–87`).
  - `date`, `shift`, `visit_type` (enum), `reason_for_visit`, `status` (enum) con distribución.
- `database/factories/PrescriptionFactory.php`
  - `medical_record_id`, `doctor_id`, `issue_date`, `diagnosis`, `notes`, `items` (array de medicamentos: `drug`, `dose`, `frequency`, `duration`, `route`, `instructions`).
  - Referencia: `app/Models/Prescription.php`.
- `database/factories/SomatometricReadingFactory.php`
  - `medical_record_id`, `appointment_id`, `user_id` (enfermero), valores vitales y observaciones.
- `database/factories/NursingAssessmentFactory.php`
  - `medical_record_id`, `user_id`, `allergies`, `personal_pathological_history`.
- `database/factories/MedicalDocumentFactory.php`
  - `medical_record_id`, `user_id`, `name`, `file_path` (dummy).

## Seeders Propuestos
- `database/seeders/DatabaseSeeder.php`
  - Orquesta el orden y cantidades; usa `call()` a seeders específicos.
- `database/seeders/UserSeeder.php`
  - Crea admin, 1–2 recepcionistas, y 5–8 médicos (roles desde `UserRole`).
- `database/seeders/ServiceSeeder.php`
  - 8–12 servicios activos con shift apropiado y responsable.
- `database/seeders/ClinicScheduleSeeder.php`
  - Genera 12–18 schedules: ayer/hoy/mañana, distintos turnos; marca 50–70% como abiertos (`is_shift_open=true`) con `opened_by` y timestamps.
- `database/seeders/PatientSeeder.php`
  - 80–120 pacientes mixtos (sexo, edad, locality, disability); status variado.
  - No crea `MedicalRecord`; usa evento (`app/Models/Patient.php:60–66`).
- `database/seeders/AppointmentSeeder.php`
  - Para hoy: 60–100 citas enlazadas a schedules abiertos, estados distribuidos: 40% pending, 20% in_progress, 30% completed, 10% cancelled.
  - Rellena `service_id`, `doctor_id`, `date`, `shift` desde el schedule.
  - Usa ticket vacío para walk-in (auto `LOCAL-YYYY-####`).
- `database/seeders/PrescriptionSeeder.php`
  - Para 70% de visitas completadas: crea receta con 1–3 medicamentos.
- `database/seeders/SomatometricReadingSeeder.php`
  - Para in_progress/completed: crea lectura con valores plausibles.
- `database/seeders/NursingAssessmentSeeder.php` (opcional)
  - Crea evaluación para 25–40% de expedientes.
- `database/seeders/MedicalDocumentSeeder.php` (opcional)
  - 1–2 documentos por 20% de expedientes.

## Reglas y Validaciones en el Seeding
- Nunca crear `MedicalRecord` directamente: tomar `patient->medicalRecord`.
- `Appointment` debe referenciar schedules abiertos y ajustar `service_id`, `doctor_id`, `date`, `shift` desde `clinic_schedule`.
- `AppointmentStatus` y `VisitType` desde enums (`app/Enums/AppointmentStatus.php:10–41`, `app/Enums/VisitType.php:5–21`).
- Recetas: respetar cast a `items` (array) y usar secuencia de folio si existe.
- Somatometría: rangos realistas (ej. TA 110–140/70–90, frecuencia 60–100, temp 36–38 °C).

## Idempotencia y Ambientes
- Limpiar tablas con `truncate` o `delete` controlado al inicio de cada seeder (cuidar FK orden).
- Usar `Model::unguard()` en `DatabaseSeeder` si conviene.
- Permitir tamaños configurables con env vars (`SEED_PATIENTS=100`, etc.).

## Comandos y Ejecución
- `php artisan migrate:fresh --seed` para poblar desde cero.
- `php artisan db:seed --class=DatabaseSeeder` para poblar sin recrear.

## Entregables
- Factories y Seeders en las rutas estándar de Laravel.
- Lógica que respeta la creación automática de `MedicalRecord` (única fuente de verdad: `Patient created`).
- Datos completos para operar dashboards y flujos (recepción y consulta).

Si apruebas, genero los archivos de factories y seeders con estados, relaciones y cantidades sugeridas, listos para ejecutar con `artisan`.