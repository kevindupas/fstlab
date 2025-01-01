<?php

namespace App\Filament\Resources\MyExperimentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected array $initialState = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('can_configure'),
                Forms\Components\Toggle::make('can_pass'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\ToggleColumn::make('can_configure')
                    ->afterStateUpdated(function ($record, $state) {
                        if (!$state && !$record->pivot->can_pass) {
                            \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                                ->where('user_id', $record->id)
                                ->delete();
                        } elseif ($state) {
                            // Si on active une permission
                            $currentStatus = \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                                ->where('user_id', $this->getOwnerRecord()->created_by)
                                ->first();

                            if ($currentStatus) {
                                \App\Models\ExperimentLink::updateOrCreate(
                                    [
                                        'experiment_id' => $this->getOwnerRecord()->id,
                                        'user_id' => $record->id,
                                    ],
                                    [
                                        'status' => $currentStatus->status,
                                        'link' => Str::random(6),
                                        'is_creator' => false,
                                        'is_secondary' => true,
                                        'is_collaborator' => false
                                    ]
                                );
                            }
                        }
                    }),
                Tables\Columns\ToggleColumn::make('can_pass')
                    ->afterStateUpdated(function ($record, $state) {
                        if (!$state && !$record->pivot->can_configure) {
                            \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                                ->where('user_id', $record->id)
                                ->delete();
                        } elseif ($state) {
                            // Si on active une permission
                            $currentStatus = \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                                ->where('user_id', $this->getOwnerRecord()->created_by)
                                ->first();

                            if ($currentStatus) {
                                \App\Models\ExperimentLink::updateOrCreate(
                                    [
                                        'experiment_id' => $this->getOwnerRecord()->id,
                                        'user_id' => $record->id,
                                    ],
                                    [
                                        'status' => $currentStatus->status,
                                        'link' => Str::random(6),
                                        'is_creator' => false,
                                        'is_secondary' => true,
                                        'is_collaborator' => false
                                    ]
                                );
                            }
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn(Builder $query) => $query->where('created_by', Auth::id()))
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Toggle::make('can_configure'),
                        Forms\Components\Toggle::make('can_pass'),
                    ])
                    ->after(function ($data) {
                        $currentStatus = \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                            ->where('user_id', $this->getOwnerRecord()->created_by)
                            ->first();

                        if ($currentStatus) {
                            \App\Models\ExperimentLink::create([
                                'experiment_id' => $this->getOwnerRecord()->id,
                                'user_id' => $data['recordId'],
                                'status' => $currentStatus->status,
                                'link' => Str::random(6),
                                'is_creator' => false,
                                'is_secondary' => true,
                                'is_collaborator' => false
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->before(function ($data, $record) {
                        $this->initialState = [
                            'can_configure' => $record->pivot->can_configure,
                            'can_pass' => $record->pivot->can_pass,
                        ];
                    })
                    ->after(function ($data, $record) {
                        if (
                            ($this->initialState['can_configure'] || $this->initialState['can_pass']) &&
                            !$data['can_configure'] && !$data['can_pass']
                        ) {
                            \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                                ->where('user_id', $record->id)
                                ->delete();
                        }
                    }),
                Tables\Actions\DetachAction::make()
                    ->before(function ($record) {
                        // Avant de détacher, on supprime le lien
                        \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                            ->where('user_id', $record->id)
                            ->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->before(function (Collection $records) {
                        // Avant de détacher en masse, on supprime tous les liens concernés
                        $records->each(function ($record) {
                            \App\Models\ExperimentLink::where('experiment_id', $this->getOwnerRecord()->id)
                                ->where('user_id', $record->id)
                                ->delete();
                        });
                    }),
            ]);
    }
}
