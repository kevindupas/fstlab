<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExperimentAccessRequestResource\Pages;
use App\Models\ExperimentAccessRequest;
use App\Models\User;
use App\Notifications\AccessRevokedNotification;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ExperimentAccessRequestResource extends Resource
{
    protected static ?string $model = ExperimentAccessRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    public static function getNavigationGroup(): string
    {
        return __('navigation.group.experiments');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.experiment-access-request.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.experiment-access-request.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.experiment-access-request.navigation_label');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.resources.experiment-access-request.form.section.status_title'))
                    ->description(__('filament.resources.experiment-access-request.form.section.status_description'))
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label(__('filament.resources.experiment-access-request.form.status.label'))
                            ->options(function ($record) {
                                if ($record->status === 'pending') {
                                    return [
                                        'pending' => __('filament.resources.experiment-access-request.form.status.options.pending'),
                                        'approved' => __('filament.resources.experiment-access-request.form.status.options.approved'),
                                        'rejected' => __('filament.resources.experiment-access-request.form.status.options.rejected'),
                                    ];
                                } elseif ($record->status === 'approved') {
                                    return [
                                        'approved' => __('filament.resources.experiment-access-request.form.status.options.approved'),
                                        'revoked' => __('filament.resources.experiment-access-request.form.status.options.revoked'),
                                    ];
                                }

                                // Pour les autres statuts (rejected, revoked), on affiche juste le statut actuel
                                return [
                                    $record->status => __("filament.resources.experiment-access-request.form.status.options.{$record->status}")
                                ];
                            })
                            ->native(false)
                            ->required()
                            ->live()
                            ->disabled(fn($record) => !in_array($record->status, ['pending', 'approved'])),

                        Forms\Components\Textarea::make('response_message')
                            ->label(__('filament.resources.experiment-access-request.form.response_message.label'))
                            ->required(fn($get) => in_array($get('status'), ['rejected', 'revoked']))
                            ->visible(fn($get) => in_array($get('status'), ['rejected', 'revoked']))
                            ->disabled(fn($record) => !in_array($record->status, ['pending', 'approved']))
                            ->columnSpanFull()
                            ->helperText(__('filament.resources.experiment-access-request.form.response_message.helper_text')),
                    ])->collapsible(),
                Forms\Components\Section::make(__('filament.resources.experiment-access-request.form.section.information_title'))
                    ->description(__('filament.resources.experiment-access-request.form.section.information_description'))
                    ->schema([
                        Placeholder::make('experiment')
                            ->label(__('filament.resources.experiment-access-request.form.experiment.label'))
                            ->content(fn($record) => $record->experiment?->name)
                            ->columnSpanFull(),

                        Placeholder::make('user')
                            ->label(__('filament.resources.experiment-access-request.form.user.label'))
                            ->content(fn($record) => $record->user?->name)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('request_message')
                            ->label(__('filament.resources.experiment-access-request.form.request_message.label'))
                            ->disabled()
                            ->columnSpanFull()
                    ])->collapsible(),


                Forms\Components\Section::make()
                    ->schema([
                        Placeholder::make(__('filament.resources.experiment-access-request.form.informations.information_access'))
                            ->content(new HtmlString(
                                '<div>' . Blade::render('<x-heroicon-o-eye class="inline-block w-5 h-5 mr-2 text-blue-500" />') .
                                    ' <strong>' . __('filament.resources.experiment-access-request.form.informations.result_access') . ':</strong> ' .
                                    __('filament.resources.experiment-access-request.form.informations.result_description') . '</div><br>' .

                                    '<div>' . Blade::render('<x-heroicon-o-play class="inline-block w-5 h-5 mr-2 text-green-500" />') .
                                    ' <strong>' . __('filament.resources.experiment-access-request.form.informations.experiment_access') . ':</strong> ' .
                                    __('filament.resources.experiment-access-request.form.informations.experiment_description') . '</div><br>' .

                                    '<div>' . Blade::render('<x-heroicon-o-document-duplicate class="inline-block w-5 h-5 mr-2 text-purple-500" />') .
                                    ' <strong>' . __('filament.resources.experiment-access-request.form.informations.duplicate_access') . ':</strong> ' .
                                    __('filament.resources.experiment-access-request.form.informations.duplicate_description') . '</div>'
                            ))
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('experiment.name')
                    ->label(__('filament.resources.experiment-access-request.table.columns.experiment'))
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('filament.resources.experiment-access-request.table.columns.user'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.resources.experiment-access-request.table.columns.type.label'))
                    ->badge()
                    ->colors([
                        'info' => 'access',
                        'warning' => 'results'
                    ])
                    ->formatStateUsing(fn($state): string => $state ? match ($state) {
                        'results' => __('filament.resources.experiment-access-request.table.columns.type.options.results'),
                        'access' => __('filament.resources.experiment-access-request.table.columns.type.options.access'),
                        default => $state
                    } : ''),

                TextColumn::make('status')
                    ->label(__('filament.resources.experiment-access-request.table.columns.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => __("filament.resources.experiment-access-request.form.status.options.$state"))
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revoked' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label(__('filament.resources.experiment-access-request.table.columns.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                // Bouton Information pour les demandes en attente
                Tables\Actions\Action::make('information')
                    ->label(__('filament.resources.experiment-access-request.table.actions.informations'))
                    ->icon('heroicon-o-information-circle')
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->url(fn($record) => Pages\EditExperimentAccessRequest::getUrl(['record' => $record])),

                // Bouton Revoke pour les demandes approuvées
                Tables\Actions\Action::make('revoke')
                    ->label(__('filament.resources.experiment-access-request.table.actions.revoke'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'approved')
                    ->form([
                        Forms\Components\Textarea::make('revoke_message')
                            ->label(__('filament.resources.experiment-access-request.table.actions.revoke_label'))
                            ->required()
                            ->placeholder(__('filament.resources.experiment-access-request.table.actions.revoke_message'))
                    ])
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.resources.experiment-access-request.table.actions.revoke'))
                    ->modalDescription(__('filament.resources.experiment-access-request.table.actions.revoke_description'))
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'revoked',
                            'response_message' => $data['revoke_message']
                        ]);

                        // Envoyer la notification
                        $record->user->notify(new AccessRevokedNotification($record));
                    }),

                // Bouton Information pour voir les détails des demandes acceptées/rejetées/révoquées
                Tables\Actions\Action::make('view')
                    ->label(__('filament.resources.experiment-access-request.table.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn($record) => $record->status !== 'pending')
                    ->url(fn($record) => Pages\EditExperimentAccessRequest::getUrl(['record' => $record]))
            ])
            ->defaultSort('created_at', 'desc');
    }

    // Limiter l'accès aux demandes
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // L'utilisateur ne peut voir que les demandes des expériences qu'il a créées
        return $query->whereHas('experiment', function ($query) {
            $query->where('created_by', Auth::id());
        });
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('experiment', function ($query) {
            $query->where('created_by', Auth::id());
        })
            ->where('status', 'pending')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Vérifie si l'utilisateur est banni
        if ($user->status === 'banned') {
            return false;
        }

        // Vérifie si le principal experimenter est banni
        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                return false;
            }
        }

        // Vérifie si l'utilisateur a des expériences
        return $user->createdExperiments()->exists();
    }

    protected function authorizeAccess(): void
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->status === 'banned') {
            abort(403, __('filament.resources.experiment-access-request.message.banned'));
        }

        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                abort(403, __('filament.resources.experiment-access-request.message.banned_secondary'));
            }
        }

        // Vérifie si l'utilisateur a des expériences
        if (!$user->createdExperiments()->exists()) {
            abort(403, __('filament.resources.experiment-access-request.message.no_access'));
        }

        parent::authorizeAccess();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->hasRole('secondary_experimenter')) {
            abort(403, __('filament.resources.experiment-access-request.message.no_access_section'));
        }

        return true;
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->hasRole('secondary_experimenter')) {
            abort(403, __('filament.resources.experiment-access-request.message.no_access_section'));
        }

        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExperimentAccessRequests::route('/'),
            'edit' => Pages\EditExperimentAccessRequest::route('/{record}/edit'),
        ];
    }
}
