<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Filament\Pages\Experiments\Statistics\ExperimentStatistics;
use App\Models\Experiment;
use App\Services\ExperimentExportHandler;
use Filament\Tables\Actions\ActionGroup;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use App\Filament\Resources\MyExperimentResource\Pages;
use App\Filament\Resources\MyExperimentResource\RelationManagers\UsersRelationManager;
use App\Models\User;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MyExperimentResource extends Resource
{
    protected static ?string $model = Experiment::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Experiments';
    protected static ?int $navigationSort = -2;
    protected static ?string $slug = 'my-experiments';

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.my_experiment.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.my_experiment.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.my_experiment.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('doi')
                ->label(__('filament.resources.my_experiment.form.doi'))
                ->required()
                ->unique(ignorable: fn($record) => $record),
            Forms\Components\Select::make('status')
                ->options([
                    'none' => __('filament.resources.my_experiment.form.status.options.none'),
                    'test' => __('filament.resources.my_experiment.form.status.options.test'),
                    'start' => __('filament.resources.my_experiment.form.status.options.start'),
                ])
                ->label(__('filament.resources.my_experiment.form.status.label'))
                ->default('none')
                ->live()
                ->afterStateUpdated(function ($state, $set, $record) {
                    if (in_array($state, ['start', 'test'])) {
                        $link = Str::random(6);
                        $set('link', $link);
                    } else {
                        $set('link', null);
                    }
                }),

            Forms\Components\TextInput::make('link')
                ->label(__('filament.resources.my_experiment.form.link.label'))
                ->extraAttributes(function ($state) {
                    $fullUrl = url("/experiment/" . $state);
                    return [
                        'x-on:click' => 'window.navigator.clipboard.writeText("' . $fullUrl . '"); $tooltip("Copié dans le presse-papier", { timeout: 1500 });',
                    ];
                })
                ->suffixAction(
                    Forms\Components\Actions\Action::make('copy')
                        ->icon('heroicon-m-clipboard')
                )
                ->visible(fn($get) => in_array($get('status'), ['start', 'test']))
                ->default(fn($record) => $record?->link)
                ->formatStateUsing(fn($state) => $state ? url("/experiment/" . $state) : '')
                ->disabled()
                ->columnSpan('full'),

            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament.resources.my_experiment.form.name'))
                        ->required()
                        ->unique(ignorable: fn($record) => $record),
                    Forms\Components\Select::make('type')
                        ->label(__('filament.resources.my_experiment.form.type.label'))
                        ->options([
                            'image' => __('filament.resources.my_experiment.form.type.options.image'),
                            'sound' => __('filament.resources.my_experiment.form.type.options.sound'),
                            'image_sound' => __('filament.resources.my_experiment.form.type.options.image_sound'),
                        ])
                        ->reactive()
                        ->required(),
                    Forms\Components\TextInput::make('button_size')
                        ->label(__('filament.resources.my_experiment.form.button_size.label'))
                        ->placeholder(__('filament.resources.my_experiment.form.button_size.placeholder'))
                        ->numeric()
                        ->default('60')
                        ->extraAttributes(["step" => "1"])
                        ->minValue(60)
                        ->maxValue(100)
                        ->suffix('px'),
                    Forms\Components\ColorPicker::make('button_color')
                        ->label(__('filament.resources.my_experiment.form.button_color.label'))
                        ->placeholder(__('filament.resources.my_experiment.form.button_color.placeholder'))
                        ->default('#ff1414'),
                ])
                ->columns(2),

            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\RichEditor::make('description')
                        ->label(__('filament.resources.my_experiment.form.description'))
                        ->disableToolbarButtons([
                            'blockquote',
                            'strike',
                            'codeBlock',
                            'attachFiles'
                        ])
                        ->columnSpan('full'),
                    Forms\Components\MarkdownEditor::make('instruction')
                        ->label(__('filament.resources.my_experiment.form.instruction'))
                        ->toolbarButtons([
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'heading',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'table',
                            'undo',
                        ])
                        ->columnSpan('full'),
                    Forms\Components\FileUpload::make('media')
                        ->label(__('filament.resources.my_experiment.form.media'))
                        ->multiple()
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes(['audio/*'])
                        ->minFiles(2)
                        ->maxFiles(30)
                        ->directory('experiments/audio')
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                ->prepend(now()->format('Y-m-d-His') . "-"),
                        )
                        ->columnSpan('full')
                        ->visible(fn($get) => $get('type') === 'sound'),
                    Forms\Components\FileUpload::make('media')
                        ->label(__('filament.resources.my_experiment.form.media'))
                        ->multiple()
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->imagePreviewHeight('250')
                        ->imageEditor()
                        ->imageEditorMode(2)
                        ->preserveFilenames()
                        ->directory('experiments/images')
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                ->prepend(now()->format('Y-m-d-His') . "-"),
                        )
                        ->minFiles(2)
                        ->maxFiles(30)
                        ->columnSpan('full')
                        ->visible(fn($get) => $get('type') === 'image'),
                    Forms\Components\FileUpload::make('media')
                        ->label(__('filament.resources.my_experiment.form.media'))
                        ->multiple()
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*', 'audio/*'])
                        ->imagePreviewHeight('250')
                        ->imageEditor()
                        ->preserveFilenames()
                        ->directory('experiments/mixed')
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                ->prepend(now()->format('Y-m-d-His') . "-"),
                        )
                        ->imageEditorMode(2)
                        ->minFiles(2)
                        ->maxFiles(30)
                        ->columnSpan('full')
                        ->visible(fn($get) => $get('type') === 'image_sound'),
                    Forms\Components\FileUpload::make('documents')
                        ->label(__('filament.resources.my_experiment.form.documents'))
                        ->multiple()
                        ->preserveFilenames()
                        ->directory('experiments/documents')
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                ->prepend(now()->format('Y-m-d-His') . "-"),
                        )
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxFiles(20)
                        ->columnSpan('full')
                ])

                ->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::getEloquentQuery()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.my_experiment.table.columns.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.resources.my_experiment.table.columns.type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.my_experiment.table.columns.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'none' => 'gray',
                        'start' => 'success',
                        'pause' => 'warning',
                        'stop' => 'danger',
                        'test' => 'info',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.my_experiment.table.columns.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('manageExperiment')
                        ->label(__('filament.resources.my_experiment.actions.session'))
                        ->color('success')
                        ->icon('heroicon-o-play')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\TextInput::make('link')
                                ->label(__('filament.resources.my_experiment.actions.session_link'))
                                ->disabled(true)
                                ->visible(fn($get) => $get('status') !== 'none')
                                ->reactive()
                                ->default(fn($record) => $record->link ? url("/experiment/{$record->link}") : 'No active session'),

                            Forms\Components\ToggleButtons::make('experimentStatus')
                                ->options([
                                    'start' => __('filament.resources.my_experiment.actions.status.start'),
                                    'pause' => __('filament.resources.my_experiment.actions.status.pause'),
                                    'stop' => __('filament.resources.my_experiment.actions.status.stop'),
                                    'test' => __('filament.resources.my_experiment.actions.status.test'),
                                ])
                                ->colors([
                                    'start' => 'success',
                                    'pause' => 'warning',
                                    'stop' => 'danger',
                                    'test' => 'info',
                                ])
                                ->icons([
                                    'start' => 'heroicon-o-play',
                                    'pause' => 'heroicon-o-pause',
                                    'stop' => 'heroicon-o-stop',
                                    'test' => 'heroicon-o-beaker',
                                ])
                                ->default(fn($record) => $record->status)
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set, $record) {
                                    if ($state === 'start' && !$record->link) {
                                        $record->link = Str::random(6);
                                        $set('link', url("/experiment/{$record->link}"));
                                    } elseif ($state === 'stop') {
                                        $record->link = null;
                                        $set('link', 'No active session');
                                    }
                                    $record->status = $state;
                                    $record->save();
                                }),
                            Placeholder::make('Informations')
                                ->content(new HtmlString(
                                    '<div>' . Blade::render('<x-heroicon-o-play class="inline-block w-5 h-5 mr-2 text-green-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.start') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.start_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-pause class="inline-block w-5 h-5 mr-2 text-yellow-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.pause') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.pause_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-stop class="inline-block w-5 h-5 mr-2 text-red-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.stop') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.stop_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-beaker class="inline-block w-5 h-5 mr-2 text-blue-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.test') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.test_desc') . '</div>'

                                ))
                                ->columnSpan('full'),
                        ])
                        ->action(function ($data, $record) {
                            Notification::make()
                                ->title(__('filament.resources.my_experiment.notifications.session_updated'))
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('export')
                        ->label(__('filament.resources.my_experiment.actions.exports'))
                        ->color('info')
                        ->icon('heroicon-o-cloud-arrow-down')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\Placeholder::make('export_placeholder')
                                ->content(new HtmlString(__('filament.resources.my_experiment.actions.export_desc')))
                                ->columnSpan('full'),
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\Toggle::make('export_json')
                                        ->label(__('filament.resources.my_experiment.actions.export_json'))
                                        ->reactive(),
                                    Forms\Components\Toggle::make('export_xml')
                                        ->label(__('filament.resources.my_experiment.actions.export_xml'))
                                        ->reactive(),
                                ])
                                ->columns(2),
                            Forms\Components\Placeholder::make('media_export_info')
                                ->content(new HtmlString(__('filament.resources.my_experiment.actions.media_export_info')))
                                ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                                ->columnSpan('full'),
                            Forms\Components\Toggle::make('include_media')
                                ->label(__('filament.resources.my_experiment.actions.include_media'))
                                ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                                ->columnSpan('full'),
                        ])
                        ->action(function ($data, Experiment $record) {
                            $handler = new ExperimentExportHandler($record);
                            Notification::make()
                                ->title(__('filament.resources.my_experiment.notifications.export_success'))
                                ->success()
                                ->send();
                            return $handler->handleExport($data);
                        }),
                    Tables\Actions\Action::make('statistics')
                        ->label(__('filament.widgets.experiment_table.actions.statistics'))
                        ->color('success')
                        ->icon('heroicon-o-chart-pie')
                        ->url(fn(Experiment $record): string =>
                        ExperimentStatistics::getUrl(['record' => $record])),
                    Tables\Actions\Action::make('details')
                        ->label(__('filament.widgets.experiment_table.actions.details'))
                        ->color('warning')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->url(fn(Experiment $record): string =>
                        ExperimentSessions::getUrl(['record' => $record])),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->dropdown(true)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->label(__('filament.resources.my_experiment.actions.more_actions'))
                    ->color('gray')
                    ->button()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('created_by', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyExperiments::route('/'),
            'create' => Pages\CreateMyExperiment::route('/create'),
            'edit' => Pages\EditMyExperiment::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user && $user->hasAnyPermission('edit_experiment_with_user')) {
            return [
                UsersRelationManager::class,
            ];
        }

        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Vérifie si l'utilisateur ou son principal est banni
        if ($user->status === 'banned') {
            return false;
        }

        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                return false;
            }
        }

        return true;
    }

    // Ajoutons aussi une vérification similaire pour bloquer l'accès complet
    protected function authorizeAccess(): void
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->status === 'banned') {
            abort(403, 'Votre compte est banni.');
        }

        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                abort(403, 'Le compte de votre expérimentateur principal est banni.');
            }
        }

        parent::authorizeAccess();
    }
}
