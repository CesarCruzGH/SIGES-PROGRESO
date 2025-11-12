## 1. Propuesta de Layout y Widgets Clave
- Encabezado con 5–6 KPIs en tarjetas y un bloque de Acciones Rápidas.
- Cuerpo en dos columnas:
  - Izquierda: "Cola en Recepción" (visitas de hoy en estado `pending`) y Acciones Rápidas (modal/formulario en el mismo dashboard).
  - Derecha: widget existente "Últimas visitas (en vivo)" con auto-actualización.
- Pie opcional con estado de turnos abiertos y alertas operativas.

Referencias actuales:
- Ruta base del panel `/dashboard`: `app\Providers\Filament\DashboardPanelProvider.php:40`.
- Página del dashboard: `app\Filament\Pages\ReceptionDashboard.php:11–22`.
- Widget actual de lista: `app\Filament\Widgets\UltimasVisitas.php:19–41, 106–110` (poll a 5s, orden y columnas).

## 2. Propuesta de "Stats Widgets" (Tarjetas de KPIs)
- Visitas de hoy: total de `Appointment` con `created_at = hoy`.
- En recepción (pendientes): conteo por `status = pending`.
- En consulta: conteo por `status = in_progress`.
- Completadas: conteo por `status = completed`.
- Canceladas: conteo por `status = cancelled`.
- Pacientes nuevos hoy: `Patient` con `created_at = hoy`.
- Turnos abiertos: `ClinicSchedule` con `is_shift_open = true`.

Notas de datos:
- Estados disponibles: `app\Enums\AppointmentStatus.php:10–13` con etiquetas y colores.
- Modelo `Patient` confirmado en `app\Models\Patient.php` (tiene `created_at`).

Implementación:
- Crear `RecepcionStats` extendiendo `Filament\Widgets\StatsOverviewWidget` con `getCards()` y `->poll('10s')` para refresco.
- Cada `Card::make()` con valor y tendencia/ayuda breve (ej. "+2 vs. última hora" si se desea, inicialmente opcional).

## 3. Propuesta de "Acciones Rápidas"
- Botones accionables, siempre visibles:
  - "Nueva visita (walk-in)" — abre modal con el esquema de `AppointmentForm` y crea `Appointment` (ticket autogenerado), referencia `app\Filament\Resources\Appointments\Schemas\AppointmentForm.php:35–105, 150–260`.
  - "Nuevo paciente" — abre modal usando el `PatientForm` embebido a través de `createOptionForm` (ya integrado en `AppointmentForm`) o como acción independiente.
  - "Buscar visita por ticket" — input que navega a `ViewAppointment` si existe.
  - "Abrir turno" / "Cerrar turno" — acciones modales reusando la lógica de `ShiftManagement`, referencias: `app\Filament\Pages\ShiftManagement.php:34–94` y `96–152`.
  - "Ver turnos abiertos" — muestra lista compacta con nombre de consultorio, servicio y médico.

Presentación:
- Implementar como `QuickActionsWidget` (extiende `Filament\Widgets\Widget`) con vista Blade y `x-filament-actions`/`x-filament::button`.
- Alternativa: definir las acciones directamente en `ReceptionDashboard::getHeaderActions()` si se prefiere en la barra superior.

## 4. Implementación Detallada

### 4.1. Reorganizar `ReceptionDashboard`
- Actualizar `getHeaderWidgets()` para incluir:
  - `RecepcionStats::class` (tarjetas KPI).
  - `QuickActionsWidget::class` (botonera de acciones rápidas).
- Mover `UltimasVisitas` al cuerpo: definir `getWidgets()` con disposición en dos columnas:
  - Izquierda: `ColaRecepcionTable::class` (nuevo widget de tabla centrado en pendientes).
  - Derecha: `UltimasVisitas::class`.

Ubicación actual y contexto:
- `app\Filament\Pages\ReceptionDashboard.php:11–18` sólo registra `UltimasVisitas` en header, retornar arreglo actualizado.

### 4.2. `RecepcionStats` (StatsOverview)
- Clase en `app\Filament\Widgets\RecepcionStats.php` que extienda `StatsOverviewWidget`.
- `protected function getCards(): array` calcula métricas del día con consultas simples.
- Añadir `protected int|string|array $columnSpan = 'full'` y `->poll('10s')` para refresco.

### 4.3. `QuickActionsWidget`
- Clase en `app\Filament\Widgets\QuickActionsWidget.php` con `protected static ?string $heading = 'Acciones rápidas'` y `protected string $view`.
- Vista Blade `resources\views\filament\widgets\quick-actions-widget.blade.php` con botones:
  - Acción modal "Nueva visita (walk-in)": reutiliza `AppointmentForm::configure(...)` para el formulario y crea el registro. Al finalizar, notifica y redirige al `ViewAppointment`.
  - "Abrir/Cerrar turno": replicar `openShiftAction()` y `closeShiftAction()` de `ShiftManagement` en acciones internas del widget.
  - "Buscar visita por ticket": input + botón que consulta `Appointment::where('ticket_number', ...)` y redirige.
  - "Nuevo paciente": acción modal mínima con campos esenciales o reutilizar `PatientForm` si se desea.

### 4.4. `ColaRecepcionTable`
- Clase en `app\Filament\Widgets\ColaRecepcionTable.php` que extienda `TableWidget`.
- `->query(Appointment::query()->whereDate('created_at', today())->where('status', AppointmentStatus::PENDING))`.
- Columnas: paciente, servicio, consultorio, ticket, hace/tiempo.
- `->recordActions([...])` con acciones:
  - "Iniciar consulta": setea `status = IN_PROGRESS` con confirmación.
  - "Cancelar": setea `status = CANCELLED` con motivo opcional.
- `->poll('5s')` para tiempo real.

### 4.5. Ajustes en `UltimasVisitas`
- Mantener como monitor general; opcionalmente añadir `recordActions` de "Ver", "Marcar en consulta", "Cancelar".
- Posiciones de columnas y tooltips ya correctos, ver `app\Filament\Widgets\UltimasVisitas.php:31–77, 80–105`.

### 4.6. Experiencia y microcopy
- Etiquetas y colores alineados al enum `AppointmentStatus` (`app\Enums\AppointmentStatus.php:14–41`).
- Tooltips claros: origen (walk-in vs turnos), servicio, consultorio.
- Confirmaciones en acciones críticas (cerrar turno, cancelar visita).

### 4.7. Actualización en tiempo real
- Usar `->poll('5s'|'10s')` en widgets de tabla y stats para refresco periódico.
- En el futuro, opcional integrar broadcasting (Echo) para push, pero el `poll` actual ya funciona (ver `UltimasVisitas.php:28`).

### 4.8. Registro automático y reutilización de formularios
- El formulario de visitas ya soporta creación de paciente en línea (`AppointmentForm.php:75–105`).
- La generación de ticket walk-in es automática (`app\Models\Appointment.php:40–47, 69–87`) y detección `isWalkIn()` (`92–95`).

Resultados esperados:
- Un "Centro de Comando" que muestra estado operacional al instante, permite crear/gestionar visitas sin navegar por menús y mantiene coherencia visual con Filament 4.

Si apruebas, preparo los archivos de widgets y la actualización de `ReceptionDashboard` siguiendo estas convenciones y añado referencias claras a cada cambio.