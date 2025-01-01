<?php

namespace App\Filament\Resources\MyExperimentResource\Pages;

use App\Filament\Resources\MyExperimentResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditMyExperiment extends EditRecord
{
    protected static string $resource = MyExperimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation(false)
                ->modalDescription(
                    fn($record) =>
                    $record->access_requests_count()->count() > 0 || $record->shared_links_count()->count() > 0
                        ? "Cette expérimentation ne peut pas être supprimée car elle est partagée ou a des demandes en attente."
                        : "Pour supprimer cette expérimentation, veuillez saisir le code ci-dessous."
                )
                ->modalSubmitActionLabel('Supprimer définitivement')
                ->hidden(fn($record) => $record->access_requests_count()->count() > 0 || $record->shared_links_count()->count() > 0)
                ->form([
                    TextInput::make('confirmation_code')
                        ->label('Code de confirmation')
                        ->required()
                        ->helperText(fn() => 'Code: ' . Str::random(6))
                        ->rules(['required', 'string', fn($get) => function ($attribute, $value, $fail) use ($get) {
                            $code = Str::random(6);
                            if ($value !== $code) {
                                $fail("Le code ne correspond pas.");
                            }
                        }])
                ]),
        ];
    }
}
