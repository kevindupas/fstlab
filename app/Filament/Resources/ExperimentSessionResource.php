<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Experiments\Sessions\ExperimentSessionDetails;
use App\Filament\Pages\Experiments\Sessions\ExperimentSessionExport;
use App\Filament\Resources\ExperimentSessionResource\Pages;
use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExperimentSessionResource extends Resource
{
    protected static ?string $model = ExperimentSession::class;
    protected static ?Experiment $experiment = null;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $experimentId = request()->query('record');
        static::$experiment = $experimentId ? Experiment::findOrFail($experimentId) : null;

        $query = parent::getEloquentQuery();

        if (static::$experiment) {
            $query->where('experiment_id', static::$experiment->id);
        }

        return $query;
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $experimentId = request()->query('record') ?? request()->route('record');

                // Si on est en train de filtrer/paginer et qu'on a déjà un experiment_id dans la requête
                if (request()->has('tableFilters') || request()->has('page')) {
                    return $query->when($experimentId, fn($q) => $q->where('experiment_id', $experimentId));
                }

                // Les vérifications normales seulement pour l'accès initial
                if (!$experimentId || !($experiment = Experiment::find($experimentId))) {
                    return $query;
                }

                $instance = new class {
                    use HasExperimentAccess;
                };
                if (!$instance->canAccessExperiment($experiment)) {
                    return $query;
                }

                return $query->where('experiment_id', $experimentId);
            })
            ->columns([
                Tables\Columns\TextColumn::make('participant_number')
                    ->label(__('filament.pages.experiments_sessions.columns.participant_number')),
                Tables\Columns\IconColumn::make('status')
                    ->icons([
                        'heroicon-o-check-circle' => fn($state): bool => $state === 'completed',
                        'heroicon-o-clock' => fn($state): bool => $state === 'created',
                    ])
                    ->colors([
                        'success' => fn($state): bool => $state === 'completed',
                        'warning' => fn($state): bool => $state === 'created',
                    ])
                    ->label(__('filament.pages.experiments_sessions.columns.status')),
                // ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.pages.experiments_sessions.columns.created_at'))
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label(__('filament.pages.experiments_sessions.columns.completed_at'))
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('experimentLink.user.name')
                    ->label(__('pages.experiments_sessions.columns.experimenter'))
                    ->description(function ($record) {
                        if (!$record->experimentLink) {
                            return '';
                        }

                        if ($record->experimentLink->user_id === Auth::id()) {
                            return __('pages.experiments_sessions.columns.experimenter_types.me');
                        }

                        if ($record->experimentLink->is_creator) {
                            return __('pages.experiments_sessions.columns.experimenter_types.creator');
                        }

                        if ($record->experimentLink->is_secondary) {
                            return __('pages.experiments_sessions.columns.experimenter_types.secondary');
                        }

                        return __('pages.experiments_sessions.columns.experimenter_types.collaborator');
                    }),

                ViewColumn::make('custom_actions')
                    ->label('Actions')
                    ->view('filament.components.custom-session-actions'),
            ])
            ->bulkActions([
                BulkAction::make('exportSelection')
                    ->label(__('filament.pages.experiments_sessions.actions.export_selection'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        $recordIds = $records->filter(fn($record) => $record->status === 'completed')
                            ->pluck('id')
                            ->toArray();

                        if (empty($recordIds)) {
                            Notification::make()
                                ->warning()
                                ->title(__('filament.pages.experiments_sessions.notifications.no_selection_completed'))
                                ->send();
                            return null;
                        }

                        session(['selected_sessions' => $recordIds]);
                        return redirect()->route('filament.admin.pages.bulk-experiment-session-export', [
                            'record' => request()->query('record')
                        ]);
                    }),

                BulkAction::make('custom_bulk_actions')
                    ->label('Supprimer les sessions sélectionnées')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function (Collection $records) {
                        $recordIds = $records->pluck('id')->toArray();

                        if (empty($recordIds)) {
                            Notification::make()
                                ->warning()
                                ->title(__('filament.pages.experiments_sessions.notifications.no_selection_completed'))
                                ->send();
                            return null;
                        }

                        session(['selected_sessions' => $recordIds]);

                        return redirect()->route('filament.admin.pages.delete-selected-sessions', [
                            'record' => request()->query('record')
                        ]);
                    })

            ])
            ->reorderable()
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExperimentSessions::route('/'),
            'create' => Pages\CreateExperimentSession::route('/create'),
            // 'edit' => Pages\EditExperimentSession::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        $experimentId = request()->query('record');
        $experiment = Experiment::find($experimentId);
        $experimentName = $experiment ? $experiment->name : '';

        return new HtmlString(
            __('filament.pages.experiments_sessions.title') .
                ($experimentName ? " : {$experimentName}" : '')
        );
    }

    public static function getModelLabel(): string
    {
        $experimentId = request()->query('record');
        $experiment = Experiment::find($experimentId);
        $experimentName = $experiment ? $experiment->name : '';

        return new HtmlString(
            __('filament.pages.experiments_sessions.title') .
                ($experimentName ? " : {$experimentName}" : '')
        );
    }

    public static function getPluralModelLabel(): string
    {
        $experimentId = request()->query('record');
        $experiment = Experiment::find($experimentId);
        $experimentName = $experiment ? $experiment->name : '';

        return new HtmlString(
            __('filament.pages.experiments_sessions.title') .
                ($experimentName ? " : {$experimentName}" : '')
        );
    }

    public static function canAccess(): bool
    {
        $experimentId = request()->query('record');
        if (!$experimentId) {
            return false;
        }

        $experiment = Experiment::find($experimentId);
        if (!$experiment) {
            return false;
        }

        $user = Auth::user();

        // Vérifie si l'utilisateur est un compte secondaire du créateur
        $isSecondaryAccount = $user->created_by === $experiment->created_by;

        // Vérifie si l'utilisateur a une demande d'accès approuvée
        $hasApprovedAccess = $experiment->accessRequests()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereIn('type', ['results', 'access'])
            ->exists();

        // Vérifie si l'utilisateur a la permission can_pass
        $hasPassPermission = $experiment->users()
            ->where('users.id', $user->id)
            ->wherePivot('can_pass', true)
            ->exists();

        // Utilisation du trait HasExperimentAccess
        $instance = new class {
            use HasExperimentAccess;
        };

        // Retourne true si :
        // - l'utilisateur a accès via HasExperimentAccess OU
        // - c'est un compte secondaire OU
        // - il a une demande d'accès approuvée OU
        // - il a la permission can_pass
        return $instance->canAccessExperiment($experiment)
            || $isSecondaryAccount
            || $hasApprovedAccess
            || $hasPassPermission;
    }
}
