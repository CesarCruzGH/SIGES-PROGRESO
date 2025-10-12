# SIGES-PROGRESO — Documentación Técnica de Resources en Filament

Este documento describe cómo está organizada la interfaz administrativa del proyecto usando Filament (Forms, Tables e Infolists), las interacciones entre los Resources, y las pautas para extender y mantener la solución.

## Índice
- Introducción y stack
- Arquitectura de Filament
- Resources y componentes
- Pacientes
- Visitas (Citas)
- Servicios
- Usuarios
- Estados y colores
- Navegación y páginas
- Reutilización de esquemas
- Interacciones clave entre Resources
- Notificaciones
- Extensión y buenas prácticas
- Comandos útiles

## Introducción y stack
- Framework: `Laravel`
- Admin UI: `Filament`
- Frontend tooling: `Vite`, `Tailwind`
- Base de datos: según tu `.env` local

Objetivo: digitalizar y administrar expedientes médicos, pacientes y visitas, con una UI consistente y reutilizable.

## Arquitectura de Filament
Cada Resource define tres piezas principales:
- `Form`: para crear/editar registros, con secciones y lógica reactiva.
- `Infolist`: para mostrar registros en modo lectura, alineado visualmente con el `Form`.
- `Table`: para listar registros con columnas, filtros y acciones.

Además, cada Resource expone sus `Pages` para listar, ver y editar.

## Resources y componentes
Estructura relevante del proyecto:
- `app/Filament/Resources/Patients/PatientResource.php`
  - Schemas: `app/Filament/Resources/Patients/Schemas/PatientForm.php`, `PatientInfolist.php`
  - Tables: `app/Filament/Resources/Patients/Tables/PatientsTable.php`
  - Pages: `ListPatients.php`, `ViewPatient.php`, `EditPatient.php`
- `app/Filament/Resources/Appointments/AppointmentResource.php`
  - Schemas: `app/Filament/Resources/Appointments/Schemas/AppointmentForm.php`
  - Tables: `app/Filament/Resources/Appointments/Tables/AppointmentsTable.php`
  - Pages: `ListAppointments.php`, `ViewAppointment.php`, `EditAppointment.php`
- `app/Filament/Resources/Services/ServiceResource.php`
  - Tables/Schemas/Páginas según la entidad Servicios
- `app/Filament/Resources/Users/UsersTable.php` (listado de usuarios)
- Enums para estados y etiquetas: `app/Enums/*` (`PatientType`, `AppointmentStatus`, `VisitType`, `Shift`, etc.)

## Pacientes
- Modelo: `App\Models\Patient`
- `Form`: define secciones (p. ej., información personal, contacto, expediente, tutor, discapacidad). Vive en `Patients/Schemas/PatientForm.php`.
- `Infolist`: replica el layout del `Form` para lectura en `Patients/Schemas/PatientInfolist.php`.
- `Table`: columnas, búsqueda y acciones en `Patients/Tables/PatientsTable.php`.
- `Pages`:
  - `ListPatients.php`: listado con filtros/acciones.
  - `ViewPatient.php`: vista detallada; título dinámico y acciones (p. ej. registrar somatometría).
  - `EditPatient.php`: edición del paciente.

Detalles prácticos:
- Columna `sex` en `PatientsTable.php` mapea `F/M` a `Femenino/Masculino` con `formatStateUsing` y colorea la badge con tokens compatibles de Filament.
- Para mantener consistencia visual, en `PatientResource::infolist` se delega a `PatientInfolist` para que el `View` sea idéntico al `Form` (orden de secciones, etiquetas y visibilidad condicional).

## Visitas (Citas)
- Modelo: `App\Models\Appointment`
- `Form`: `Appointments/Schemas/AppointmentForm.php` configura selección/creación de expediente, paciente y servicio, horarios, tipo de visita, etc.
- `Table`: `Appointments/Tables/AppointmentsTable.php` lista citas con filtros por fecha/estado y acciones.
- `Pages`: `ListAppointments.php`, `ViewAppointment.php`, `EditAppointment.php`.

Patrones útiles:
- Uso de `Enums` para estados (`AppointmentStatus`, `VisitType`, `Shift`) facilita colorear badges y estandarizar etiquetas.
- Formularios con dependencias (p. ej. al seleccionar `Patient`, se precargan datos relacionados del `MedicalRecord`).

## Servicios
- Modelo: `App\Models\Service`
- Resource: `ServiceResource.php` con sus páginas, tablas y formularios.
- Recomendado asignar colores y categorías usando `Enums` si aplica.

## Usuarios
- Modelo: `App\Models\User`
- Listado: `UsersTable.php` con columnas básicas y acciones administrativas.

## Estados y colores
- Filament usa tokens de color: `primary`, `success`, `warning`, `danger`, `info`, `gray`.
- Ejemplo de mapeo (sin afectar valores en BD):
  - `sex`: `F/Femenino` → `warning`, `M/Masculino` → `primary`.
  - Usa callbacks que normalicen el estado (`strtolower`, `trim`) para evitar caer en `gray` por mayúsculas/espacios.

## Navegación y páginas
- Cada Resource registra sus `Pages` estándar: `List`, `View`, `Edit`.
- En `ViewPatient.php` se personaliza el título con `getTitle()` y se agregan acciones (p. ej. `addSomatometricReading`) con formularios inline.
- Las acciones pueden notificar (`Filament\Notifications\Notification`) y crear registros relacionados (`somatometricReadings()` del modelo).

## Reutilización de esquemas
- `Form` y `Infolist` se definen en `Schemas/*` y se reutilizan en `PatientResource::form` y `PatientResource::infolist`.
- Buen patrón: delegar directamente a la clase de esquema, por ejemplo:

```php
// En PatientResource.php
public static function infolist(\Filament\Infolists\Infolist $schema): \Filament\Infolists\Infolist
{
    return \App\Filament\Resources\Patients\Schemas\PatientInfolist::configure($schema);
}
```

Así el `View` queda idéntico al `Form` y se centraliza el mantenimiento.

## Interacciones clave entre Resources
- Paciente ↔ Expediente médico ↔ Visitas: la cita puede vincularse a un expediente y a un servicio.
- Navegación cruzada: desde tablas y vistas se puede saltar a registros relacionados mediante acciones o enlaces.
- Estados y filtros consistentes: los `Enums` garantizan etiquetas y colores uniformes en formularios, infolists y tablas.

## Notificaciones
- `App\Notifications\NewVisitRegistered` envía avisos cuando se registra una nueva visita.
- Las acciones de página (`Action::make(...)`) pueden disparar `Notification::make()->success()->send()` para feedback inmediato.

## Extensión y buenas prácticas
- Reutiliza esquemas (`Schemas/*`) para evitar duplicación.
- Normaliza estados con `formatStateUsing` y `color()` en columnas.
- Usa `Enums` para etiquetas y mapeos; colorea con tokens de Filament.
- Mantén `Pages` enfocadas: `List` para filtros/acciones masivas, `View` para lectura fiel al `Form`, `Edit` para cambios.
- Al agregar nuevas entidades, replica el patrón: `Resource`, `Schemas/Form`, `Schemas/Infolist`, `Tables`, `Pages`.
- Limpia cachés si no ves cambios en UI: `php artisan optimize:clear`.

## Comandos útiles
- Instalar dependencias frontend: `npm install`
- Ejecutar build dev: `npm run dev`
- Enlazar almacenamiento: `php artisan storage:link`
- Migrar y seed: `php artisan migrate --seed`
- Limpiar cachés: `php artisan optimize:clear`
- Servidor de desarrollo: `php artisan serve`

---

Si necesitas que el `View Patient` sea idéntico al `Form`, asegúrate de que `PatientInfolist` refleje las mismas secciones y que `PatientResource::infolist` delegue a esa clase. Para el campo `sex`, mantén el mapeo `F/M` → `Femenino/Masculino` con colors de Filament y normalización del estado para una visualización robusta.