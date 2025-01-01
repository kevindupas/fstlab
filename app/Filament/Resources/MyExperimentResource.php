<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
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
use App\Models\ExperimentLink;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
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
        /** @var \App\Models\User */
        $user = Auth::user();
        return $form->schema([
            Forms\Components\Section::make(__('filament.resources.my_experiment.section_base.heading'))
                ->description(__('filament.resources.my_experiment.section_base.description'))
                ->schema([
                    Forms\Components\TextInput::make('doi')
                        ->label(__('filament.resources.my_experiment.form.doi'))
                        ->placeholder(__('filament.resources.my_experiment.form.doi_placeholder'))
                        ->helperText(__('filament.resources.my_experiment.form.doi_helper'))
                        ->unique(ignorable: fn($record) => $record),

                    Forms\Components\Toggle::make('howitwork_page')
                        ->label(__('filament.resources.my_experiment.form.howitworks'))
                        ->helperText(__('filament.resources.my_experiment.form.howitworks_helper'))
                        ->visible(fn() => $user->hasRole('supervisor')),

                    Forms\Components\Select::make('status')
                        ->options([
                            'stop' => __('filament.resources.my_experiment.form.status.options.stop'),
                            'test' => __('filament.resources.my_experiment.form.status.options.test'),
                            'start' => __('filament.resources.my_experiment.form.status.options.start'),
                        ])
                        ->label(__('filament.resources.my_experiment.form.status.label'))
                        ->helperText(__('filament.resources.my_experiment.form.status.helper_text'))
                        ->default('stop')
                        ->required()
                        ->live()
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record) {
                                $experimentLink = ExperimentLink::where('experiment_id', $record->id)
                                    ->where('user_id', Auth::id())
                                    ->first();

                                if ($experimentLink) {
                                    $component->state($experimentLink->status);
                                }
                            }
                        })
                        ->afterStateUpdated(function ($state, $set, $record, $livewire) {
                            if (!$state) {
                                $state = 'stop';
                            }

                            $linkValue = null;
                            if ($state === 'test' || $state === 'start') {
                                $link = Str::random(6);
                                $livewire->data['link'] = url("/experiment/{$link}");
                                $livewire->data['temp_link'] = $link;
                                $linkValue = $link;
                            }

                            if ($state !== 'test') {
                                $set('howitwork_page', false);
                            }

                            // Déterminer les flags
                            $user = Auth::user();

                            // Mise à jour ou création du lien
                            if ($linkValue || $state === 'stop') {
                                ExperimentLink::updateOrCreate(
                                    [
                                        'experiment_id' => $record ? $record->id : $livewire->record->id,
                                        'user_id' => $user->id,
                                    ],
                                    [
                                        'status' => $state,
                                        'link' => $linkValue,
                                        'is_creator' => true,
                                        'is_secondary' => false,
                                        'is_collaborator' => false
                                    ]
                                );
                            }
                        })
                ])->collapsible(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.general_section.heading'))
                ->description(__('filament.resources.my_experiment.general_section.description'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament.resources.my_experiment.form.name'))
                        ->helperText(__('filament.resources.my_experiment.form.name_helper'))
                        ->required(),
                    //->unique(ignorable: fn($record) => $record),

                    Forms\Components\Select::make('type')
                        ->label(__('filament.resources.my_experiment.form.type.label'))
                        ->helperText(__('filament.resources.my_experiment.form.type.helper_text'))
                        ->options([
                            'image' => __('filament.resources.my_experiment.form.type.options.image'),
                            'sound' => __('filament.resources.my_experiment.form.type.options.sound'),
                            'image_sound' => __('filament.resources.my_experiment.form.type.options.image_sound'),
                        ])
                        ->reactive()
                        ->required(),
                ])->collapsible(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.apparence_section.heading'))
                ->description(__('filament.resources.my_experiment.apparence_section.description'))
                ->schema([
                    Forms\Components\TextInput::make('button_size')
                        ->label(__('filament.resources.my_experiment.form.button_size.label'))
                        ->helperText(__('filament.resources.my_experiment.form.button_size.helper_text'))
                        ->numeric()
                        ->default('60')
                        ->extraAttributes(["step" => "1"])
                        ->minValue(60)
                        ->maxValue(100)
                        ->suffix('px'),

                    Forms\Components\ColorPicker::make('button_color')
                        ->label(__('filament.resources.my_experiment.form.button_color.label'))
                        ->helperText(__('filament.resources.my_experiment.form.button_color.helper_text'))
                        ->default('#ff1414'),
                ])->collapsible(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.section_description.heading'))
                ->description(__('filament.resources.my_experiment.section_description.description'))
                ->schema([
                    Forms\Components\RichEditor::make('description')
                        ->label(__('filament.resources.my_experiment.form.description'))
                        ->helperText(__('filament.resources.my_experiment.form.description_helper'))
                        ->disableToolbarButtons([
                            'blockquote',
                            'strike',
                            'codeBlock',
                            'attachFiles'
                        ])
                        ->columnSpan('full'),

                    Forms\Components\MarkdownEditor::make('instruction')
                        ->label(__('filament.resources.my_experiment.form.instructions'))
                        ->helperText(__('filament.resources.my_experiment.form.instructions_helper'))
                        ->columnSpan('full'),
                ])->collapsible(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.section_media.heading'))
                ->description(__('filament.resources.my_experiment.section_media.description'))
                ->schema([
                    // Les trois composants FileUpload existants avec maxFileSize
                    Forms\Components\FileUpload::make('media')
                        ->label(__('filament.resources.my_experiment.form.media'))
                        ->helperText(__('filament.resources.my_experiment.form.media_sound_helper'))
                        ->multiple()
                        ->maxSize(20000)
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'audio/*',
                        ])
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
                        ->helperText(__('filament.resources.my_experiment.form.media_image_helper'))
                        ->multiple()
                        ->maxSize(20000)
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'image/*',
                        ])
                        ->imagePreviewHeight('250')
                        //->imageEditor()
                        //->imageEditorMode(2)
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
                        ->helperText(__('filament.resources.my_experiment.form.media_image_sound_helper'))
                        ->multiple()
                        ->maxSize(20000)
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'image/*',
                            'audio/*',
                        ])
                        //->imagePreviewHeight('250')
                        //->imageEditor()
                        ->preserveFilenames()
                        ->directory('experiments/mixed')
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                ->prepend(now()->format('Y-m-d-His') . "-"),
                        )
                        ->minFiles(2)
                        ->maxFiles(30)
                        ->columnSpan('full')
                        ->visible(fn($get) => $get('type') === 'image_sound'),
                ])->collapsible(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.section_documents.heading'))
                ->description(__('filament.resources.my_experiment.section_documents.description'))
                ->schema([
                    Forms\Components\FileUpload::make('documents')
                        ->label(__('filament.resources.my_experiment.form.documents'))
                        ->helperText(__('filament.resources.my_experiment.form.documents_helper'))
                        ->maxSize(20000) // 20Mo
                        ->multiple()
                        ->preserveFilenames()
                        ->directory('experiments/documents')
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                ->prepend(now()->format('Y-m-d-His') . "-"),
                        )
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ])
                        ->maxFiles(20)
                        ->columnSpan('full')
                ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $isSupevisor = $user->hasRole('supervisor');
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
                    ->label(__('filament.resources.my_experiment.table.columns.type.label'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sound' => __('filament.resources.my_experiment.table.columns.type.options.sound'),
                        'image' => __('filament.resources.my_experiment.table.columns.type.options.image'),
                        'image_sound' => __('filament.resources.my_experiment.table.columns.type.options.image_sound'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.my_experiment.table.columns.status.label'))
                    ->badge()
                    ->state(function ($record) {
                        $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                            ->where('user_id', Auth::id())
                            ->first();
                        return $experimentLink ? $experimentLink->status : 'stop';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'start' => __('filament.resources.my_experiment.table.columns.status.options.start'),
                        'pause' => __('filament.resources.my_experiment.table.columns.status.options.pause'),
                        'stop' => __('filament.resources.my_experiment.table.columns.status.options.stop'),
                        'test' => __('filament.resources.my_experiment.table.columns.status.options.test'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'start' => 'success',
                        'pause' => 'warning',
                        'stop' => 'danger',
                        'test' => 'info',
                    }),
                Tables\Columns\IconColumn::make('howitwork_page')
                    ->label(__('filament.resources.my_experiment.table.columns.howitworks'))
                    ->visible(fn() => $isSupevisor)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
                                ->visible(fn($get) => $get('experimentStatus') !== 'stop')
                                ->reactive()
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    return $experimentLink && $experimentLink->link
                                        ? url("/experiment/{$experimentLink->link}")
                                        : 'No active session';
                                }),

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
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                        ->where('user_id', Auth::id())
                                        ->first();
                                    return $experimentLink ? $experimentLink->status : 'stop';
                                })
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set, $record) {
                                    // Récupérer le lien existant
                                    $existingLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    // Gestion du lien d'expérimentation selon l'état
                                    $linkValue = match ($state) {
                                        'start' => $existingLink?->link ?? Str::random(6), // Nouveau lien si pas de lien existant
                                        'pause' => $existingLink?->link ?? Str::random(6), // Garde le même lien
                                        'test' => Str::random(6), // Toujours un nouveau lien
                                        'stop' => null, // Pas de lien
                                        default => null,
                                    };

                                    // Mise à jour ou création du lien
                                    $experimentLink = ExperimentLink::updateOrCreate(
                                        [
                                            'experiment_id' => $record->id,
                                            'user_id' => Auth::id(),
                                        ],
                                        [
                                            'status' => $state,
                                            'link' => $linkValue,
                                            'is_creator' => true,
                                            'is_secondary' => false,
                                            'is_collaborator' => false
                                        ]
                                    );

                                    // Mise à jour de l'affichage
                                    if ($experimentLink->link) {
                                        $set('link', url("/experiment/{$experimentLink->link}"));
                                    } else {
                                        $set('link', 'No active session');
                                    }

                                    // Gestion du howitwork_page
                                    if ($state !== 'test') {
                                        $record->howitwork_page = false;
                                        $record->save();
                                    }
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
                    Tables\Actions\Action::make('contact_principal')
                        ->label(__('filament.resources.my_experiment.actions.contact'))
                        ->icon('heroicon-o-envelope')
                        ->url(fn(Experiment $record) => "/admin/contact-principal?experiment={$record->id}")
                        ->visible(fn() => $user->hasRole('secondary_experimenter')),
                    Tables\Actions\Action::make('export')
                        ->label(__('filament.resources.my_experiment.actions.exports'))
                        ->color('gray')
                        ->icon('heroicon-o-cloud-arrow-down')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\Placeholder::make(__('filament.resources.my_experiment.actions.export.label'))
                                ->content(new HtmlString(__('filament.resources.my_experiment.actions.export.desc')))
                                ->columnSpan('full'),
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\Toggle::make('export_json')
                                        ->label(__('filament.resources.my_experiment.actions.export.json'))
                                        ->reactive(),
                                    Forms\Components\Toggle::make('export_xml')
                                        ->label(__('filament.resources.my_experiment.actions.export.xml'))
                                        ->reactive(),
                                ])
                                ->columns(2),
                            Forms\Components\Placeholder::make('media_export_info')
                                ->content(new HtmlString(__('filament.resources.my_experiment.actions.export.media_info')))
                                ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                                ->columnSpan('full'),
                            Forms\Components\Toggle::make('include_media')
                                ->label(__('filament.resources.my_experiment.actions.export.include_media'))
                                ->visible(fn($get) => $get('export_json') || $get('export_xml'))
                                ->columnSpan('full'),
                        ])
                        ->action(function ($data, Experiment $record) {
                            $handler = new ExperimentExportHandler($record);
                            Notification::make()
                                ->title(__('filament.resources.my_experiment.notifications.export.success'))
                                ->success()
                                ->send();
                            return $handler->handleExport($data);
                        }),
                    Tables\Actions\Action::make('statistics')
                        ->label(__('filament.resources.my_experiment.actions.statistics'))
                        ->color('success')
                        ->icon('heroicon-o-chart-pie')
                        ->url(fn(Experiment $record): string =>
                        ExperimentStatistics::getUrl(['record' => $record])),

                    Tables\Actions\Action::make('results')
                        ->label(__('filament.resources.my_experiment.actions.results'))
                        ->color('gray')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->url(
                            fn(Experiment $record): string =>
                            route('filament.admin.resources.experiment-sessions.index', [
                                'record' => $record->id,
                            ])
                        ),
                    Tables\Actions\Action::make('view')
                        ->label(__('filament.resources.my_experiment.actions.details'))
                        ->icon('heroicon-o-eye')
                        ->url(fn(Experiment $record): string =>
                        ExperimentDetails::getUrl(['record' => $record])),
                    Tables\Actions\EditAction::make()->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation(false)
                        ->modalSubmitActionLabel(__('filament.resources.my_experiment.actions.delete.heading'))
                        ->modalDescription(
                            fn($record) =>
                            $record->access_requests_count()->count() > 0 || $record->shared_links_count()->count() > 0
                                ? __('filament.resources.my_experiment.actions.delete.desc_issues_delete')
                                : __('filament.resources.my_experiment.actions.delete.confirm_delete')
                        )
                        ->hidden(fn($record) => $record->access_requests_count()->count() > 0 || $record->shared_links_count()->count() > 0)
                        ->form([
                            TextInput::make('confirmation_code')
                                ->label(__('filament.resources.my_experiment.actions.delete.code_confirm'))
                                ->required()
                                ->helperText(function () {
                                    $code = Str::random(6);
                                    return __('filament.resources.my_experiment.actions.delete.code') . ' : ' . $code;
                                })
                                ->rules(['required', 'string', fn($get) => function ($attribute, $value, $fail) use ($get) {
                                    $code = Str::random(6);
                                    if ($value !== $code) {
                                        $fail(__('filament.resources.my_experiment.actions.delete.code_fail'));
                                    }
                                }])
                        ])
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
            ->where(function ($query) {
                $query->where('created_by', Auth::id())
                    ->orWhereHas('users', function ($q) {
                        $q->where('user_id', Auth::id())
                            ->where(function ($q) {
                                $q->where('can_configure', true)
                                    ->orWhere('can_pass', true);
                            });
                    });
            });
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Auth::id() === $ownerRecord->created_by;
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

        /** @var \App\Models\Experiment */
        $record = request()->route('record');
        $experiment = \App\Models\Experiment::find($record);

        if (
            $user &&
            $user->hasRole('principal_experimenter') &&
            $experiment &&
            $experiment->created_by === $user->id
        ) {
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
            return false;
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

    public static function canAccess(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->hasRole('secondary_experimenter')) {
            abort(403, "En tant que compte secondaire, vous n'avez pas accès à cette section. Utilisez le tableau de bord pour gérer vos expérimentations.");
        }

        return true;
    }

    // Et aussi pour bien s'assurer que même l'accès à la liste est bloqué
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->hasRole('secondary_experimenter')) {
            abort(403, "En tant que compte secondaire, vous n'avez pas accès à cette section. Utilisez le tableau de bord pour gérer vos expérimentations.");
        }

        return true;
    }
}
