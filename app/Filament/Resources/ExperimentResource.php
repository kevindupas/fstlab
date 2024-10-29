<?php

namespace App\Filament\Resources;

use App\Filament\Pages\ExperimentSessions;
use App\Filament\Resources\ExperimentResource\Pages;
use App\Filament\Resources\ExperimentResource\RelationManagers\UsersRelationManager;
use App\Models\Experiment;
use App\Services\ExperimentExportHandler;
use Filament\Actions\CreateAction;
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
use RalphJSmit\Filament\MediaLibrary\Forms\Components\MediaPicker;

class ExperimentResource extends Resource
{

    use UsesResourceLock;

    protected static ?string $model = Experiment::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->unique(ignorable: fn($record) => $record),
                    Forms\Components\Select::make('type')
                        ->options([
                            'image' => 'Image',
                            'sound' => 'Sound',
                        ])
                        ->reactive()
                        ->required(),
                    Forms\Components\TextInput::make('button_size')
                        ->visible(fn($get) => $get('type') === 'sound'),
                    Forms\Components\ColorPicker::make('button_color')
                        ->visible(fn($get) => $get('type') === 'sound'),
                ])
                ->columnSpan([
                    'sm' => 2,
                ]),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\MarkdownEditor::make('description')
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
                        ])->columnSpan('full'),
                    Forms\Components\Radio::make('status')
                        ->options([
                            'none' => 'No',
                            'start' => 'Yes',
                        ])
                        ->label('Start Experiment ?')->inlineLabel(true)->default('none')->inline(),
                    Forms\Components\TextInput::make('link')
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('copyCostToPrice')
                                ->icon('heroicon-m-clipboard')
                                ->action(function (Set $set, $state) {
                                    $set('link', $state);
                                })
                        )
                        ->visible(fn($get) => $get('link') !== null && $get('status') !== 'none')
                        ->default(function ($livewire) {
                            return $livewire->record && $livewire->record->link ? url("/experiment/" . $livewire->record->link) : 'No active session';
                        })
                        ->disabled(true)
                        ->columnSpan('full'),
                    Forms\Components\FileUpload::make('media')
                        ->multiple()
                        ->acceptedFileTypes(['image/*', 'audio/*'])
                        ->columnSpan('full'),
                    // MediaPicker::make('media')
                    //     ->label('Choose media(s)')
                    //     ->required()
                    //     ->multiple()
                    //     ->acceptedFileTypes(['image/*', 'audio/*'])
                    //     ->columnSpan('full'),
                ])
                ->columnSpan('full'),
        ]);
    }


    public static function table(Table $table): Table
    {

        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('type')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'sound' => 'success',
                    'image' => 'info',
                }),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'none' => 'info',
                    'start' => 'success',
                    'pause' => 'warning',
                    'stop' => 'danger',
                }),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime(),
        ])
            ->filters([
                //
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
                    ->label('Session')
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->form([
                        Forms\Components\TextInput::make('link')
                            ->label('Experiment Link')
                            ->disabled(true)
                            ->visible(fn($get) => $get('status') !== 'none')
                            ->reactive()  // S'assure que ce champ est réactif
                            ->default(fn($record) => $record->link ? url("/experiment/{$record->link}") : 'No active session'),

                        Forms\Components\ToggleButtons::make('experimentStatus')
                            ->options([
                                'start' => 'Start',
                                'pause' => 'Pause',
                                'stop' => 'Stop'
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
                            ->reactive()  // Rend le ToggleButtons réactif
                            ->afterStateUpdated(function ($state, $set, $record) {
                                if ($state === 'start' && !$record->link) {
                                    $record->link = Str::random(40);
                                    $set('link', url("/experiment/{$record->link}"));
                                } elseif ($state === 'stop') {
                                    $record->link = null;
                                    $set('link', 'No active session');
                                }
                                $record->status = $state;
                                $record->save();
                            }),
                        Placeholder::make('Informations')
                            ->content(new HtmlString('
                                <div>' . Blade::render('<x-heroicon-o-play class="inline-block w-5 h-5 mr-2 text-green-500" />') . ' <strong>Start:</strong> Activates the session and generates a unique link if one does not exist. The session becomes accessible to participants.</div><br>
                                <div>' . Blade::render('<x-heroicon-o-pause class="inline-block w-5 h-5 mr-2 text-yellow-500" />') . ' <strong>Pause:</strong> Temporarily suspends the session. The link remains active, but participants cannot continue the session until it is resumed.</div><br>
                                <div>' . Blade::render('<x-heroicon-o-stop class="inline-block w-5 h-5 mr-2 text-red-500" />') . ' <strong>Stop:</strong> Ends the session and deactivates the link. To reactivate the session, you must start it again, which generates a new link.</div>
                            '))
                            ->columnSpan('full'),


                    ])
                    ->action(function ($data, $record, $livewire) {
                        Notification::make()
                            ->title('Experiment session status updated.')
                            ->success()
                            ->send();
                    }),
                Action::make('export')
                    ->label('Export')
                    ->color('info')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->visible(function ($record) {
                        $isCreator = $record->created_by == Auth::user()->id;
                        $canConfigure = $record->users()->where('user_id', Auth::user()->id)->first()?->pivot?->can_configure ?? false;
                        return $isCreator || $canConfigure;
                    })
                    ->form([
                        Forms\Components\Placeholder::make('export_placeholder')
                            ->content(new HtmlString('<div>Select the format in which you want to export the experiment data. You can choose JSON, XML, or both.</div>'))
                            ->columnSpan('full'),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Toggle::make('export_json')
                                    ->label('Export as JSON')
                                    ->reactive(),
                                Forms\Components\Toggle::make('export_xml')
                                    ->label('Export as XML')
                                    ->reactive(),
                            ])
                            ->columnSpan([
                                'sm' => 2,
                            ]),
                        Forms\Components\Placeholder::make('media_export_info')
                            ->content(new HtmlString('<div>Including media will add all associated media files to the export. This option will create a zip folder containing your configuration and the media files.</div>'))
                            ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                            ->columnSpan('full'),
                        Forms\Components\Toggle::make('include_media')
                            ->label('Include Media')
                            ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                            ->columnSpan('full'),
                    ])
                    ->modalWidth('xl')
                    ->action(function ($data, Experiment $record, $livewire) {
                        $handler = new ExperimentExportHandler($record);
                        Notification::make()
                            ->title('Exported successfully')
                            ->success()
                            ->send();
                        $downloadResponse = $handler->handleExport($data);
                        return $downloadResponse;
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
            'sessions' => Pages\ExperimentSessions::route('/{record}/sessions'),
            'session-details' => Pages\ExperimentSessionDetails::route('/session/{record}'),
        ];
    }

    protected function beforeCreate(): void {}

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
}
