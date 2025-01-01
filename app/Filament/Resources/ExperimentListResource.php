<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Filament\Resources\ExperimentListResource\Pages;
use App\Models\Experiment;
use App\Models\User;
use App\Traits\HasExperimentAccess;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExperimentListResource extends Resource
{
    use HasExperimentAccess;

    protected static ?string $model = Experiment::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $slug = 'experiments-list';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        $userId = request()->query('filter_user');
        $username = $userId ? User::find($userId)->name : null;

        return $username
            ? __('filament.resources.experiment_list.titleFilter', ['username' => $username])
            : __('filament.resources.experiment_list.title');
    }

    public static function getPluralModelLabel(): string
    {
        $userId = request()->query('filter_user');
        $username = $userId ? User::find($userId)->name : null;

        return $username
            ? __('filament.resources.experiment_list.titleFilter', ['username' => $username])
            : __('filament.resources.experiment_list.title');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.resources.experiment_list.title');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = Experiment::query()
                    ->select([
                        'experiments.*',
                        DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiments.id = experiment_sessions.experiment_id) as sessions_count')
                    ])
                    ->with(['creator']);

                // Si un filtre utilisateur est présent
                if ($userId = request()->query('filter_user')) {
                    $query->where('created_by', $userId);
                    return $query;
                }

                /** @var \App\Models\User */
                $user = Auth::user();
                $trait = new class {
                    use HasExperimentAccess;
                };

                if ($user->hasRole('supervisor')) {
                    // Récupérer les IDs des principaux et secondaires
                    $principalIds = $trait->getPrincipalIds();
                    $secondaryIds = $trait->getSecondaryIds($principalIds);

                    $query->whereHas('creator', function ($q) {
                        // On exclut les superviseurs
                        $q->whereDoesntHave('roles', function ($q) {
                            $q->where('name', 'supervisor');
                        });
                    })
                        ->where(function ($q) use ($user, $principalIds, $secondaryIds) {
                            $q->whereIn('created_by', $principalIds)
                                ->orWhereIn('created_by', $secondaryIds)
                                // Ajouter les expérimentations des principaux qui se sont inscrits eux-mêmes
                                ->orWhereHas('creator', function ($q) {
                                    $q->whereNull('created_by')
                                        ->whereHas('roles', function ($q) {
                                            $q->where('name', 'principal_experimenter');
                                        });
                                })
                                // Exclure les propres expérimentations du superviseur
                                ->where('created_by', '!=', $user->id);
                        });
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('filament.resources.experiment_list.column.created_by'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.experiment_list.column.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.resources.experiment_list.column.type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sound' => __('filament.resources.experiment_list.column.sound'),
                        'image' => __('filament.resources.experiment_list.column.image'),
                        'image_sound' => __('filament.resources.experiment_list.column.image_sound'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.experiment_list.column.status'))
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $experimentLink = $record->links()
                            ->where('user_id', $record->created_by)
                            ->first();
                        return $experimentLink ? $experimentLink->status : 'stop';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'start' => __('filament.resources.experiment_list.column.start'),
                        'pause' => __('filament.resources.experiment_list.column.pause'),
                        'stop' => __('filament.resources.experiment_list.column.stop'),
                        'test' => __('filament.resources.experiment_list.column.test'),
                        default => __('filament.resources.experiment_list.column.none'),
                    })
                    ->colors([
                        'success' => 'start',
                        'warning' => 'pause',
                        'danger' => 'stop',
                        'info' => 'test',
                        'gray' => 'none'
                    ])
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('experiment_links', function ($join) {
                                $join->on('experiments.id', '=', 'experiment_links.experiment_id')
                                    ->where('experiment_links.user_id', '=', DB::raw('experiments.created_by'));
                            })
                            ->orderBy('experiment_links.status', $direction);
                    }),
                // Tables\Columns\TextColumn::make('sessions_count')
                //     ->label(__('filament.resources.experiment_list.column.sessions_count'))
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.experiment_list.column.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('filament.resources.experiment_list.column.action'))
                    ->icon('heroicon-o-eye')
                    ->url(fn(Experiment $record): string =>
                    ExperimentDetails::getUrl(['record' => $record]))
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Si l'utilisateur a la permission spécifique, il a accès
        if ($user->hasPermissionTo('view_experiments_list')) {
            return true;
        }

        // Sinon, il doit avoir un des rôles autorisés ET la permission
        return false;
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        if ($user->hasRole('supervisor')) {
            return true;
        } else {
            abort(403, __('filament.resources.experiments_list.access_denied'));
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExperimentLists::route('/'),
            'create' => Pages\CreateExperimentList::route('/create'),
            'edit' => Pages\EditExperimentList::route('/{record}/edit'),
        ];
    }
}
