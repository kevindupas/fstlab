<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MyExperimentResource\Pages\EditMyExperiment;
use App\Filament\Resources\SharedExperimentResource\Pages;
use App\Models\Experiment;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SharedExperimentResource extends Resource
{
    protected static ?string $model = Experiment::class;
    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'Experiments';
    protected static ?int $navigationSort = -1;
    protected static ?string $slug = 'shared-experiments';

    public static function getModelLabel(): string
    {
        $userId = request()->query('filter_user');
        $username = $userId ? User::find($userId)->name : null;

        return __('filament.resources.secondary_user.title', ['username' => $username]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                /** @var \App\Models\User */
                $user = Auth::user();

                $query = Experiment::query()
                    ->select([
                        'experiments.*',
                        'experiment_user.can_pass',
                        'experiment_user.can_configure',
                        DB::raw('(SELECT COUNT(*) FROM experiment_user WHERE experiment_user.experiment_id = experiments.id) as sessions_count')
                    ])
                    ->join('experiment_user', 'experiments.id', '=', 'experiment_user.experiment_id');

                if ($userId = request()->query('filter_user')) {
                    $query->where('experiment_user.user_id', $userId);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.secondary_user.table.column.name'))
                    ->searchable()
                    ->words(4)
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->label(__('filament.resources.secondary_user.table.column.type.label'))
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sound' => __('filament.resources.secondary_user.table.column.type.options.sound'),
                        'image' => __('filament.resources.secondary_user.table.column.type.options.image'),
                        'image_sound' => __('filament.resources.secondary_user.table.column.type.options.image_sound'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->state(function ($record) {
                        $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                            ->where('user_id', Auth::id())
                            ->first();
                        return $experimentLink ? $experimentLink->status : 'stop';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'start' => __('filament.resources.secondary_user.table.column.status.options.start'),
                        'pause' => __('filament.resources.secondary_user.table.column.status.options.pause'),
                        'stop' => __('filament.resources.secondary_user.table.column.status.options.stop'),
                        'test' => __('filament.resources.secondary_user.table.column.status.options.test'),
                        default => __('filament.resources.secondary_user.table.column.status.options.stop'),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'start' => 'success',
                        'pause' => 'warning',
                        'stop' => 'danger',
                        'test' => 'info',
                    }),
                Tables\Columns\IconColumn::make('can_pass')
                    ->label(__('filament.resources.secondary_user.table.column.can_pass'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_configure')
                    ->label(__('filament.resources.secondary_user.table.column.can_configure'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.secondary_user.table.column.created_at'))
                    ->dateTime()
                    ->sortable(),

            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('actions.edit_experiment'))
                    ->color('warning')

                    ->icon('heroicon-o-pencil')
                    ->url(fn(Experiment $record): string =>
                    EditMyExperiment::getUrl(['record' => $record]))
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return $user->hasRole('principal_experimenter');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSharedExperiments::route('/'),
            'create' => Pages\CreateSharedExperiment::route('/create'),
            // 'edit' => Pages\EditSharedExperiment::route('/{record}/edit'),
        ];
    }
}
