<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->action(fn () => $this->save())
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar edición')
                ->modalSubheading('Se guardarán los cambios de la visita.')
                ->modalSubmitActionLabel('Sí, guardar')
                ->modalCancelActionLabel('Cancelar'),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Tras guardar, redirige al listado principal de visitas
        return AppointmentResource::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        // Notificación de confirmación al editar
        return Notification::make()
            ->title('Visita actualizada correctamente')
            ->body('Los cambios de la visita se han guardado exitosamente.')
            ->success();
    }
            public function getTitle(): string
    {
        // La variable $this->record contiene la visita que se está viendo.
        // Construimos un título más descriptivo.
        return "Editar detalles de la Visita #{$this->getRecord()->ticket_number}";
    }
}
