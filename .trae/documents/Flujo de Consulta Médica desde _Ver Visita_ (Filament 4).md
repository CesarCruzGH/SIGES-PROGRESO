## Objetivo UX

* En “Ver Visita” (`/dashboard/appointments/{id}`), cuando la visita esté en estado `En consulta`, mostrar un botón destacado “Registrar Consulta”.

* Al pulsarlo, abrir un modal con la “Nota de Consulta” que incluya:

  * Diagnóstico (textarea)

  * Plan de Tratamiento (textarea)

  * Receta (Repeater de medicamentos con campos clave)

* Tras guardar: crear la receta asociada al expediente, marcar la visita como completada y ofrecer opción de imprimir.

## Ubicación y Condiciones

* Página: `app/Filament/Resources/Appointments/Pages/ViewAppointment.php:17–35` — ya tiene `getHeaderActions()` para acciones.

* Nueva acción “Registrar Consulta” en `getHeaderActions()` con `visible(fn () => $this->record->status === AppointmentStatus::IN_PROGRESS)`.

* (Opcional) Validar que el usuario sea el médico asignado de la visita (`$this->record->doctor_id === auth()->id()`), y que el consultorio asociado tenga turno abierto (`clinicSchedule.is_shift_open`).

## Modelo de Datos (Reutilización)

* Usar el modelo `Prescription` existente para persistir la consulta + receta:

  * Campos: `medical_record_id`, `doctor_id`, `folio`, `issue_date`, `diagnosis`, `notes`, `items` (json) — ver `app/Models/Prescription.php` y migración.

  * Relación a expediente: `MedicalRecord::prescriptions()` — ya implementado.

* Mapear “Diagnóstico” → `prescriptions.diagnosis` y “Plan de Tratamiento” → `prescriptions.notes`.

* Receta: guardar cada medicamento como item dentro de `prescriptions.items` (array casteado).

## Formulario Modal (Nota de Consulta)

* Definir acción `Action::make('register_consultation')` con `->form([...])`:

  * `Textarea::make('diagnosis')->label('Diagnóstico')->required()->rows(4)`

  * `Textarea::make('treatment_plan')->label('Plan de Tratamiento')->rows(4)`

  * \`Repeater::make('items')->label('Medicamentos')->minItems(1)->addActionLabel('Añadir Medicamento')->columnSpanFull()->schema(\[

    * `TextInput::make('drug')->label('Medicamento')->required()`

    * `TextInput::make('dose')->label('Dosis')->placeholder('500 mg')`

    * `TextInput::make('frequency')->label('Frecuencia')->placeholder('Cada 8 horas')`

    * `TextInput::make('duration')->label('Duración')->placeholder('5 días')`

    * `Select::make('route')->label('Vía')->options(['Oral' => 'Oral','IM' => 'IM','IV' => 'IV','Topical' => 'Tópica'])`

    * `Textarea::make('instructions')->label('Indicaciones')->rows(2)`
      ])

  * (Opcional) `Toggle::make('complete_visit')->label('Marcar visita como completada')->default(true)`

## Acción y Flujo de Guardado

* En `->action(function (array $data) { ... })` de la acción del modal:

  * Crear `Prescription` ligado al expediente de la visita:

    * `Prescription::create([
      'medical_record_id' => $this->record->medical_record_id,
      'doctor_id' => auth()->id(),
      'issue_date' => now(),
      'diagnosis' => $data['diagnosis'],
      'notes' => $data['treatment_plan'] ?? null,
      'items' => $data['items'],
      ])`

  * Si `complete_visit` es true: `Appointment::whereKey($this->record->id)->update(['status' => AppointmentStatus::COMPLETED])`.

  * Notificación de éxito.

  * Redirección conveniente:

    * Opción A: Permanecer en “Ver Visita” y mostrar botón “Imprimir receta”.

    * Opción B: Redirigir a `MedicalRecord` → Relation Manager de Prescripciones.

## Impresión / Descarga de Receta

* Reutilizar la ruta existente para descargar receta: `routes/web.php` tiene `prescription.download`.

* Tras crear `Prescription`, mostrar notificación con enlace:

  * `prescription.download` con `copyType` (original/copia), o añadir botón en la vista.

* Si se requiere botón permanente, añadir en `ViewAppointment` una acción “Imprimir receta” visible si existe receta reciente.

## Seguridad y Permisos

* Validar permisos:

  * Solo roles de médico pueden ver/usar “Registrar Consulta”.

  * Validar que la visita está en `IN_PROGRESS`.

  * (Opcional) Validar coincidencia de médico asignado.

* Manejar concurrencia: si otro proceso marca la visita como completada, bloquear creación duplicada.

## Estados y Validaciones

* Reglas:

  * `diagnosis` requerido.

  * `items` min 1; cada item requiere `drug`.

* Estados de la visita:

  * Tras completar, `status = COMPLETED`.

  * Mantener `reason_for_visit` histórico.

* Feedback:

  * Notificaciones Filament al éxito/validaciones.

## Integración UI en "Ver Visita"

* En `getHeaderActions()` de `ViewAppointment`:

  * Añadir `Action::make('register_consultation')` con `->visible(...)`, `->form([...])`, `->action(...)`.

  * Mantener `EditAction` y “Completar Expediente” existentes.

## Pruebas y Verificación

* Casos:

  * Visita `IN_PROGRESS`: acción visible y funcional; crea receta con items; marca visita como completada.

  * Visita `PENDING` o `COMPLETED`: acción oculta.

  * Médico no asignado: acción oculta (si se aplica restricción).

* Verificar aparición en Relation Manager de Prescripciones del expediente.

* Descargar/Imprimir receta vía ruta existente.

## Entregables

* Actualización de `ViewAppointment.php` con acción “Registrar Consulta”.

* Formulario modal con diagnóstico, plan y repeater de medicamentos.

* Persistencia en `Prescription` y actualización del estado de `Appointment`.

* Notificación y enlace de impresión.

* (Opcional) Restricciones de acceso por rol y médico asignado.

## Referencias del código

* Vista de Visita: `app/Filament/Resources/Appointments/Pages/ViewAppointment.php:17–35`

* Estados de visita: `app/Enums/AppointmentStatus.php:10–41`

* Recetas: `app/Models/Prescription.php` (JSON `items`, relación `medicalRecord`) y migración `database/migrations/2025_11_12_000200_create_prescriptions_table.php`

* Expediente médico: relaciones en `app/Models/MedicalRecord.php`

* Descarga de receta: `routes/web.php` (`prescription.download`)

