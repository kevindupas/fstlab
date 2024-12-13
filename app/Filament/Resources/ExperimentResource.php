<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExperimentResource\Pages;
use App\Models\Experiment;
use App\Services\ExperimentExportHandler;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Kenepa\ResourceLock\Resources\Pages\Concerns\UsesResourceLock;
use Illuminate\Support\Str;

class ExperimentResource extends Resource
{

    use UsesResourceLock;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Experiment::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    public static function getModelLabel(): string
    {
        return __('filament.resources.experiment.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.experiment.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Radio::make('status')
                ->options([
                    'none' => __('filament.resources.experiment.form.status.options.none'),
                    'start' => __('filament.resources.experiment.form.status.options.start'),
                ])
                ->label(__('filament.resources.experiment.form.status.label'))
                ->inlineLabel(true)
                ->default('none')
                ->inline(),
            Forms\Components\TextInput::make('link')
                ->label(__('filament.resources.experiment.form.link.label'))
                ->suffixAction(
                    Forms\Components\Actions\Action::make('copyCostToPrice')
                        ->icon('heroicon-m-clipboard')
                        ->action(function (Set $set, $state) {
                            $set('link', $state);
                        })
                )
                ->visible(fn($get) => $get('link') !== null && $get('status') !== 'none')
                ->default(function ($livewire) {
                    return $livewire->record && $livewire->record->link
                        ? url("/experiment/" . $livewire->record->link)
                        : __('filament.resources.experiment.form.link.no_active_session');
                })
                ->disabled(true)
                ->columnSpan('full'),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament.resources.experiment.form.name'))
                        ->required()
                        ->unique(ignorable: fn($record) => $record),
                    Forms\Components\Select::make('type')
                        ->label(__('filament.resources.experiment.form.type.label'))
                        ->options([
                            'image' => __('filament.resources.experiment.form.type.options.image'),
                            'sound' => __('filament.resources.experiment.form.type.options.sound'),
                            'image_sound' => __('filament.resources.experiment.form.type.options.image_sound'),
                        ])
                        ->reactive()
                        ->required(),
                    Forms\Components\TextInput::make('button_size')
                        ->label(__('filament.resources.experiment.form.button_size.label'))
                        ->placeholder(__('filament.resources.experiment.form.button_size.placeholder'))
                        ->numeric()
                        ->extraAttributes(["step" => "0.01"])
                        ->minValue(1)
                        ->maxValue(100)
                        ->suffix('px'),
                    Forms\Components\ColorPicker::make('button_color')
                        ->label(__('filament.resources.experiment.form.button_color.label'))
                        ->placeholder(__('filament.resources.experiment.form.button_color.placeholder'))
                        ->default('#ff1414'),
                ])
                ->columnSpan([
                    'sm' => 2,
                ]),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\MarkdownEditor::make('description')
                        ->label(__('filament.resources.experiment.form.description'))
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
                        ->label(__('filament.resources.experiment.form.media'))
                        ->multiple()
                        ->acceptedFileTypes(['audio/*'])
                        ->minFiles(2)
                        ->maxFiles(30)
                        ->columnSpan('full')
                        ->visible(fn($get) => $get('type') === 'sound'),
                    Forms\Components\FileUpload::make('media')
                        ->label(__('filament.resources.experiment.form.media'))
                        ->multiple()
                        ->acceptedFileTypes(['image/*'])
                        ->imagePreviewHeight('250')
                        ->imageEditor()
                        ->imageEditorMode(2)
                        ->minFiles(2)
                        ->maxFiles(30)
                        ->columnSpan('full')
                        ->visible(fn($get) => $get('type') === 'image'),
                    Forms\Components\FileUpload::make('media')
                        ->label(__('filament.resources.experiment.form.media'))
                        ->multiple()
                        ->acceptedFileTypes(['image/*', 'audio/*'])
                        ->imagePreviewHeight('250')
                        ->imageEditor()
                        ->imageEditorMode(2)
                        ->minFiles(2)
                        ->maxFiles(30)
                        ->columnSpan('full')
                        ->visible(fn($get) => $get('type') === 'image_sound'),
                ])
                ->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label(__('filament.resources.experiment.table.columns.name')),
            Tables\Columns\TextColumn::make('type')
                ->label(__('filament.resources.experiment.table.columns.type'))
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'sound' => 'success',
                    'image' => 'info',
                    'image_sound' => 'warning',
                }),
            Tables\Columns\TextColumn::make('status')
                ->label(__('filament.resources.experiment.table.columns.status'))
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'none' => 'info',
                    'start' => 'success',
                    'pause' => 'warning',
                    'stop' => 'danger',
                }),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('filament.resources.experiment.table.columns.created_at'))
                ->dateTime(),
            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('filament.resources.experiment.table.columns.updated_at'))
                ->dateTime(),
        ])
            ->modifyQueryUsing(function ($query) {
                /** @var \App\Models\User */
                $user = Auth::user();

                if ($user->hasRole('supervisor')) {
                } else if ($user->hasRole('principal_experimenter')) {
                    $query->where('created_by', Auth::user()->id);
                } else {
                    $query->whereHas('users', function ($q) {
                        $q->where('users.id', Auth::user()->id);
                    });
                }
            })
            ->actions([
                Action::make('manageExperiment')
                    ->label(__('filament.resources.experiment.table.actions.manageExperiment.label'))
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->form([
                        Forms\Components\TextInput::make('link')
                            ->label(__('filament.resources.experiment.table.actions.manageExperiment.fields.link'))
                            ->disabled(true)
                            ->visible(fn($get) => $get('status') !== 'none')
                            ->reactive()
                            ->default(fn($record) => $record->link
                                ? url("/experiment/{$record->link}")
                                : __('filament.resources.experiment.form.link.no_active_session')),

                        Forms\Components\ToggleButtons::make('experimentStatus')
                            ->options([
                                'start' => __('filament.resources.experiment.table.actions.manageExperiment.fields.experimentStatus.options.start'),
                                'pause' => __('filament.resources.experiment.table.actions.manageExperiment.fields.experimentStatus.options.pause'),
                                'stop' => __('filament.resources.experiment.table.actions.manageExperiment.fields.experimentStatus.options.stop')
                            ])
                            ->colors([
                                'start' => 'success',
                                'pause' => 'warning',
                                'stop' => 'danger',
                            ])
                            ->icons([
                                'start' => 'heroicon-o-play',
                                'pause' => 'heroicon-o-pause',
                                'stop' => 'heroicon-o-stop',
                            ])
                            ->default(fn($record) => $record->status)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $record) {
                                if ($state === 'start' && !$record->link) {
                                    $record->link = Str::random(40);
                                    $set('link', url("/experiment/{$record->link}"));
                                } elseif ($state === 'stop') {
                                    $record->link = null;
                                    $set('link', __('filament.resources.experiment.form.link.no_active_session'));
                                }
                                $record->status = $state;
                                $record->save();
                            }),
                        Placeholder::make('Informations')
                            ->content(new HtmlString(
                                '<div>' . Blade::render('<x-heroicon-o-play class="inline-block w-5 h-5 mr-2 text-green-500" />') .
                                    ' <strong>' . __('filament.resources.experiment.table.actions.manageExperiment.fields.experimentStatus.options.start') . ':</strong> ' .
                                    __('filament.resources.experiment.table.actions.manageExperiment.fields.info.start') . '</div><br>' .

                                    '<div>' . Blade::render('<x-heroicon-o-pause class="inline-block w-5 h-5 mr-2 text-yellow-500" />') .
                                    ' <strong>' . __('filament.resources.experiment.table.actions.manageExperiment.fields.experimentStatus.options.pause') . ':</strong> ' .
                                    __('filament.resources.experiment.table.actions.manageExperiment.fields.info.pause') . '</div><br>' .

                                    '<div>' . Blade::render('<x-heroicon-o-stop class="inline-block w-5 h-5 mr-2 text-red-500" />') .
                                    ' <strong>' . __('filament.resources.experiment.table.actions.manageExperiment.fields.experimentStatus.options.stop') . ':</strong> ' .
                                    __('filament.resources.experiment.table.actions.manageExperiment.fields.info.stop') . '</div>'
                            ))
                            ->columnSpan('full'),
                    ])
                    ->action(function ($data, $record, $livewire) {
                        Notification::make()
                            ->title(__('filament.resources.experiment.table.actions.manageExperiment.notification'))
                            ->success()
                            ->send();
                    }),
                Action::make('export')
                    ->label(__('filament.resources.experiment.table.actions.export.label'))
                    ->color('info')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->visible(function ($record) {
                        $isCreator = $record->created_by == Auth::user()->id;
                        $canConfigure = $record->users()->where('user_id', Auth::user()->id)->first()?->pivot?->can_configure ?? false;
                        return $isCreator || $canConfigure;
                    })
                    ->form([
                        Forms\Components\Placeholder::make('export_placeholder')
                            ->content(new HtmlString(__('filament.resources.experiment.table.actions.export.fields.placeholder')))
                            ->columnSpan('full'),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Toggle::make('export_json')
                                    ->label(__('filament.resources.experiment.table.actions.export.fields.json'))
                                    ->reactive(),
                                Forms\Components\Toggle::make('export_xml')
                                    ->label(__('filament.resources.experiment.table.actions.export.fields.xml'))
                                    ->reactive(),
                            ])
                            ->columnSpan([
                                'sm' => 2,
                            ]),
                        Forms\Components\Placeholder::make('media_export_info')
                            ->content(new HtmlString(__('filament.resources.experiment.table.actions.export.fields.media_info')))
                            ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                            ->columnSpan('full'),
                        Forms\Components\Toggle::make('include_media')
                            ->label(__('filament.resources.experiment.table.actions.export.fields.include_media'))
                            ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                            ->columnSpan('full'),
                    ])
                    ->modalWidth('xl')
                    ->action(function ($data, Experiment $record, $livewire) {
                        $handler = new ExperimentExportHandler($record);
                        Notification::make()
                            ->title(__('filament.resources.experiment.table.actions.export.success'))
                            ->success()
                            ->send();
                        return $handler->handleExport($data);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExperiments::route('/'),
            'create' => Pages\CreateExperiment::route('/create'),
            'edit' => Pages\EditExperiment::route('/{record}/edit'),
        ];
    }

    protected function beforeCreate(): void {}

    // public static function getRelations(): array
    // {
    //     /** @var \App\Models\User */
    //     $user = Auth::user();

    //     if ($user && $user->hasAnyPermission('edit_experiment_with_user')) {
    //         return [
    //             UsersRelationManager::class,
    //         ];
    //     }

    //     return [];
    // }
}
