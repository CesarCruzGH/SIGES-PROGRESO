## Problema
- La firma `protected function infolist(Infolist $infolist): Infolist` en la página `ViewAppointment` no coincide con Filament 4. La documentación indica que el infolist debe definirse en el Resource con `public static function infolist(Schema $schema): Schema`.

## Plan de Corrección
- Eliminar el método `infolist()` de `app/Filament/Resources/Appointments/Pages/ViewAppointment.php`.
- Implementar `public static function infolist(Schema $schema): Schema` en `app/Filament/Resources/Appointments/AppointmentResource.php`, siguiendo la convención del proyecto (delegar a clase en `Schemas`).
- Crear `app/Filament/Resources/Appointments/Schemas/AppointmentInfolist.php` con:
  - `use Filament\Schemas\Components\Section;`
  - `use Filament\Infolists\Components\TextEntry;`
  - `use Filament\Infolists\Components\RepeatableEntry;`
  - Sección "Nota de Consulta" que muestra la última `Prescription` del expediente asociado a la visita:
    - `Diagnóstico`: `TextEntry` con `->state(fn ($record) => latestPrescription($record)?->diagnosis)`
    - `Plan de Tratamiento`: `TextEntry` con `->state(fn ($record) => latestPrescription($record)?->notes)`
    - `Receta`: `RepeatableEntry` con `->state(fn ($record) => latestPrescription($record)?->items ?? [])` y entradas `drug`, `dose`, `frequency`, `duration`, `route`, `instructions`.
  - `->visible(fn ($record) => latestPrescription($record) !== null)`
- Añadir helper interno `latestPrescription($record)` (closure) que consulta `Prescription::where('medical_record_id', $record->medical_record_id)->latest('id')->first()`.

## Mantener Acciones en Página
- Conservar en `ViewAppointment` los botones de impresión ya implementados: “Imprimir receta (Paciente)” y “Imprimir receta (Institución)”, visibles si existe receta.
- Conservar “Registrar Consulta” con validación de médico asignado y estado `IN_PROGRESS`.

## Verificación
- Abrir `ViewAppointment` de una visita con consulta registrada:
  - Sección "Nota de Consulta" visible en el contenido (infolist del recurso), mostrando diagnóstico, plan y receta.
  - Botones de impresión disponibles en el header.
- Confirmar que no hay advertencias de firma Intelephense.

¿Apruebas que mueva la visualización a `AppointmentResource::infolist()` y cree `AppointmentInfolist` siguiendo las convenciones del proyecto?