<?php

namespace App\Filament\Pages\Experiments\Lists;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Models\Experiment;
use App\Traits\HasExperimentAccess;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ExperimentsList extends Page implements HasTable
{
    use InteractsWithTable;
    use HasExperimentAccess;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $slug = 'experiments-list';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.experiments.lists.experiments-list';

    public static function getNavigationLabel(): string
    {
        return __('filament.pages.experiment_list.title');
    }
    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('filament.pages.experiment_list.title'));
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

    public function table(Table $table): Table
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

                if ($user->hasRole('supervisor')) {
                    $principalIds = $this->getPrincipalIds();
                    $secondaryIds = $this->getSecondaryIds($principalIds);

                    $query->whereIn('created_by', $principalIds)
                        ->orWhereIn('created_by', $secondaryIds)
                        ->whereHas('creator', function ($q) {
                            $q->whereDoesntHave('roles', function ($q) {
                                $q->where('name', 'supervisor');
                            });
                        });
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('filament.pages.experiment_list.column.created_by'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.pages.experiment_list.column.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.pages.experiment_list.column.type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sound' => __('filament.pages.experiment_list.column.sound'),
                        'image' => __('filament.pages.experiment_list.column.image'),
                        'image_sound' => __('filament.pages.experiment_list.column.image_sound'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.pages.experiment_list.column.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'start' => __('filament.pages.experiment_list.column.start'),
                        'pause' => __('filament.pages.experiment_list.column.pause'),
                        'stop' => __('filament.pages.experiment_list.column.stop'),
                        'test' => __('filament.pages.experiment_list.column.test'),
                        'none' => __('filament.pages.experiment_list.column.none'),
                        default => $state
                    })
                    ->colors([
                        'success' => 'start',
                        'warning' => 'pause',
                        'danger' => 'stop',
                        'info' => 'test',
                        'gray' => 'none'
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label(__('filament.pages.experiment_list.column.sessions_count'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.pages.experiment_list.column.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('filament.pages.experiment_list.column.action'))
                    ->icon('heroicon-o-eye')
                    ->url(fn(Experiment $record): string =>
                    ExperimentDetails::getUrl(['record' => $record]))
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearFilter')
                ->label('Retirer le filtre')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->url('/admin/experiments-list')
                ->visible(fn() => request()->has('filter_user')),
        ];
    }
}
