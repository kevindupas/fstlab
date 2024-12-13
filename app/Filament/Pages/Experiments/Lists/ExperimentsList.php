<?php

namespace App\Filament\Pages\Experiments\Lists;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Models\Experiment;
use App\Traits\HasExperimentAccess;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExperimentsList extends Page implements HasTable
{
    use InteractsWithTable;
    use HasExperimentAccess;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Liste des Expérimentations';
    protected static ?string $slug = 'experiments-list';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.experiments.lists.experiments-list';

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
                    ->label('Créateur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'success' => 'start',
                        'warning' => 'pause',
                        'danger' => 'stop',
                        'info' => 'none',
                    ]),
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label('Nombre de sessions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([

                Tables\Actions\Action::make('view')
                    ->label('Voir l\'expérimentation')
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
        return [];
    }
}
