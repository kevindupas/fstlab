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
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\MaxWidth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Tables\Actions\DeleteAction;

class MyExperimentResource extends Resource
{
    protected static ?string $model = Experiment::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?int $navigationSort = -2;
    protected static ?string $slug = 'my-experiments';

    // protected static ?string $navigationGroup = null;

    public static function getNavigationGroup(): string
    {
        return __('navigation.group.experiments');
    }


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

    public static function getRecordRouteKeyName(): ?string
    {
        return 'id';
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
                        ->default(fn() => Str::random(10))
                        ->disabled()
                        ->dehydrated()
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
                        ->native(false)
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record) {
                                $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
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

                            if ($state === 'test' || $state === 'start') {
                                $link = Str::random(6);
                                $set('link', url("/experiment/{$link}"));
                                $set('temp_link', $link);
                            }

                            if ($state !== 'test') {
                                $set('howitwork_page', false);
                            }
                        }),

                    Forms\Components\TextInput::make('link')
                        ->label(__('filament.resources.my_experiment.form.link'))
                        ->helperText(__('filament.resources.my_experiment.form.link_helper'))
                        ->extraAttributes(function ($state) {
                            return [
                                'x-on:click' => 'window.navigator.clipboard.writeText("' . $state . '"); $tooltip("' . __('filament.resources.my_experiment.form.link_copied') . '", { timeout: 1500 }); $wire.$dispatch("link-copied");',
                            ];
                        })
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('copy')
                                ->icon('heroicon-m-clipboard')
                                ->action(function () {
                                    \Filament\Notifications\Notification::make()
                                        ->title(__('filament.resources.my_experiment.form.link_copied'))
                                        ->body(__('filament.resources.my_experiment.form.link_copied_success'))
                                        ->success()
                                        ->send();
                                })
                        )
                        ->visible(fn($get) => in_array($get('status'), ['start', 'test']))
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record) {
                                $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                    ->where('user_id', Auth::id())
                                    ->first();

                                if ($experimentLink && $experimentLink->link) {
                                    $component->state(url("/experiment/{$experimentLink->link}"));
                                }
                            }
                        })
                        ->disabled()
                        ->columnSpan('full'),
                    Forms\Components\Toggle::make('is_public')
                        ->label(__('filament.resources.my_experiment.form.is_public'))
                        ->helperText(__('filament.resources.my_experiment.form.is_public_helper')),

                    Forms\Components\Select::make('language')
                        ->label(__('filament.resources.my_experiment.form.language'))
                        ->helperText(__('filament.resources.my_experiment.form.language_helper'))
                        ->native(false)
                        ->default('fr')
                        ->options([
                            'fr' => 'Français',
                            'en' => 'English',
                        ])
                ])->collapsible(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.general_section.heading'))
                ->description(__('filament.resources.my_experiment.general_section.description'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament.resources.my_experiment.form.name'))
                        ->helperText(__('filament.resources.my_experiment.form.name_helper'))
                        ->required(),

                    Forms\Components\Toggle::make('is_random')
                        ->label(__('filament.resources.my_experiment.form.is_random'))
                        ->helperText(__('filament.resources.my_experiment.form.is_random_helper')),

                    Forms\Components\Select::make('type')
                        ->label(__('filament.resources.my_experiment.form.type.label'))
                        ->helperText(__('filament.resources.my_experiment.form.type.helper_text'))
                        ->options([
                            'image' => __('filament.resources.my_experiment.form.type.options.image'),
                            'sound' => __('filament.resources.my_experiment.form.type.options.sound'),
                            'image_sound' => __('filament.resources.my_experiment.form.type.options.image_sound'),
                        ])
                        ->native(false)
                        ->reactive()
                        ->required(),
                ])->collapsed(),

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
                ])->collapsed(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.section_description.heading'))
                ->description(__('filament.resources.my_experiment.section_description.description'))
                ->schema([
                    Forms\Components\MarkdownEditor::make('description')
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
                ])->collapsed(),

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
                        ->reorderable()
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
                        ->panelLayout('grid')
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
                ])->collapsed(),

            Forms\Components\Section::make(__('filament.resources.my_experiment.section_responsible.heading'))
                ->description(__('filament.resources.my_experiment.section_responsible.description'))
                ->schema([
                    TextInput::make('responsible_institution')
                        ->label(__('filament.resources.my_experiment.form.responsible_institution'))
                        ->placeholder(__('filament.resources.my_experiment.form.responsible_institution_placeholder'))
                        ->helperText(__('filament.resources.my_experiment.form.responsible_institution_helper'))
                        ->required()
                ])->collapsed(),

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
                ])->collapsed(),
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
                    ->select([
                        'experiments.*',
                        DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiment_sessions.experiment_id = experiments.id) as sessions_count')
                    ])
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
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label(__('filament.resources.my_experiment.table.columns.sessions_count'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.my_experiment.table.columns.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('copy_link')
                        ->label(__('actions.copy_link'))
                        ->icon('heroicon-o-link')
                        ->extraAttributes(function ($record) {
                            $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                ->where('user_id', Auth::id())
                                ->first();

                            if ($experimentLink && $experimentLink->link) {
                                $url = url("/experiment/{$experimentLink->link}");
                                return [
                                    'data-copy-url' => $url,
                                    'x-on:click' => 'window.navigator.clipboard.writeText($el.dataset.copyUrl);',
                                ];
                            }
                            return [];
                        })
                        ->action(function ($record) {
                            $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                ->where('user_id', Auth::id())
                                ->first();

                            if ($experimentLink && $experimentLink->link) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('actions.copy_link'))
                                    ->body(__('actions.link_copied_to_clipboard'))
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('actions.copy_link'))
                                    ->body(__('actions.no_link_available') . ' ' . __('actions.please_start_experiment', ['action' => __('actions.manage_session.label')]))
                                    ->warning()
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('manageExperiment')
                        ->label(__('actions.manage_session.label'))
                        ->color('success')
                        ->icon('heroicon-o-play')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\TextInput::make('link')
                                ->label(__('actions.manage_session.session_link'))
                                ->disabled(true)
                                ->visible(fn($get) => $get('experimentStatus') !== 'stop')
                                ->reactive()
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    return $experimentLink && $experimentLink->link
                                        ? url("/experiment/{$experimentLink->link}")
                                        : __('actions.manage_session.no_session');
                                }),

                            Forms\Components\ToggleButtons::make('experimentStatus')
                                ->label(__('actions.manage_session.status'))
                                ->options([
                                    'start' => __('actions.manage_session.options.start'),
                                    'pause' => __('actions.manage_session.options.pause'),
                                    'stop' => __('actions.manage_session.options.stop'),
                                    'test' => __('actions.manage_session.options.test'),
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
                                        $set('link', __('actions.manage_session.no_session'));
                                    }

                                    // Gestion du howitwork_page
                                    if ($state !== 'test') {
                                        $record->howitwork_page = false;
                                        $record->save();
                                    }
                                }),
                            Placeholder::make(__('actions.manage_session.information'))
                                ->content(new HtmlString(
                                    '<div>' . Blade::render('<x-heroicon-o-play class="inline-block w-5 h-5 mr-2 text-green-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.start') . ':</strong> ' .
                                        __('actions.manage_session.start_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-pause class="inline-block w-5 h-5 mr-2 text-yellow-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.pause') . ':</strong> ' .
                                        __('actions.manage_session.pause_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-stop class="inline-block w-5 h-5 mr-2 text-red-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.stop') . ':</strong> ' .
                                        __('actions.manage_session.stop_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-beaker class="inline-block w-5 h-5 mr-2 text-blue-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.test') . ':</strong> ' .
                                        __('actions.manage_session.test_desc') . '</div>'

                                ))
                                ->columnSpan('full'),
                        ])
                        ->action(function ($data, $record) {
                            Notification::make()
                                ->title(__('actions.manage_session.success'))
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('contact_principal')
                        ->label(__('actions.contact_principal'))
                        ->icon('heroicon-o-envelope')
                        ->url(fn(Experiment $record) => "/admin/contact-principal?experiment={$record->id}")
                        ->visible(fn() => $user->hasRole('secondary_experimenter')),
                    Tables\Actions\Action::make('export')
                        ->label(__('actions.save_experiment'))
                        ->color('gray')
                        ->icon('heroicon-o-cloud-arrow-down')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\Placeholder::make(__('actions.export_experiment.label'))
                                ->content(new HtmlString(__('actions.export_experiment.desc')))
                                ->columnSpan('full'),
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\Toggle::make('export_json')
                                        ->label(__('actions.export_experiment.json'))
                                        ->reactive(),
                                    Forms\Components\Toggle::make('export_xml')
                                        ->label(__('actions.export_experiment.xml'))
                                        ->reactive(),
                                    Forms\Components\Toggle::make('export_yaml')
                                        ->label(__('actions.export_experiment.yaml'))
                                        ->reactive(),
                                ])
                                ->columns(3),
                            Forms\Components\Placeholder::make(__('actions.export_experiment.media_export_info'))
                                ->content(new HtmlString(__('actions.export_experiment.media_info')))
                                ->visible(fn($get) => $get('export_json') || $get('export_xml') || $get('export_yaml'))
                                ->columnSpan('full'),
                            Forms\Components\Toggle::make('include_media')
                                ->label(__('actions.export_experiment.include_media'))
                                ->visible(fn($get) => $get('export_json') || $get('export_xml') || $get('export_yaml'))
                                ->columnSpan('full'),
                        ])
                        ->action(function ($data, Experiment $record) {
                            $handler = new ExperimentExportHandler($record);
                            Notification::make()
                                ->title(__('actions.export_experiment.success'))
                                ->success()
                                ->send();
                            return $handler->handleExport($data);
                        }),
                    Tables\Actions\Action::make('statistics')
                        ->label(__('actions.statistics'))
                        ->color('success')
                        ->icon('heroicon-o-chart-pie')
                        ->url(fn(Experiment $record): string =>
                        ExperimentStatistics::getUrl(['record' => $record])),

                    Tables\Actions\Action::make('results')
                        ->label(__('actions.results'))
                        ->color('gray')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->url(
                            fn(Experiment $record): string =>
                            route('filament.admin.resources.experiment-sessions.index', [
                                'record' => $record->id,
                            ])
                        ),
                    Tables\Actions\Action::make('view')
                        ->label(__('actions.details_experiment'))
                        ->icon('heroicon-o-eye')
                        ->url(fn(Experiment $record): string =>
                        ExperimentDetails::getUrl(['record' => $record])),
                    Tables\Actions\EditAction::make()->color('warning')->label(__('actions.edit_experiment')),
                    Tables\Actions\DeleteAction::make()
                        ->label(__('actions.delete_experiment'))
                        ->requiresConfirmation(false)
                        ->modalHeading(__('actions.delete_experiment'))
                        ->modalSubmitActionLabel(__('actions.delete_experiment'))
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
                                    session(['delete_confirmation_code' => $code]);
                                    return __('filament.resources.my_experiment.actions.delete.code') . ' : ' . $code;
                                })
                                ->rules(['required', 'string'])
                        ])
                        ->before(function (array $data, DeleteAction $action) {
                            if ($data['confirmation_code'] !== session('delete_confirmation_code')) {
                                Notification::make()
                                    ->title(__('filament.resources.my_experiment.actions.delete.code_fail'))
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        })
                ])
                    ->dropdownWidth(MaxWidth::ExtraSmall)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->label(__('actions.actions'))
                    ->color('gray')
                    ->button()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Si on est dans une page d'édition, on permet l'accès aux expérimentations où on a can_configure
        if (request()->route()->getName() === 'filament.admin.resources.my-experiments.edit') {
            return $query;
        }

        // Sinon, on ne montre que les expérimentations qu'on a créées
        return $query->where('created_by', Auth::id());
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Auth::id() === $ownerRecord->created_by
            || $ownerRecord->users()
            ->where('users.id', Auth::id())
            ->where('can_configure', true)  // C'est can_configure !
            ->exists();
    }

    public static function resolveRecordRouting($record): array
    {
        // Cette méthode permet de retrouver l'enregistrement même s'il n'est pas
        // dans la query principale (qui ne montre que ceux qu'on a créés)
        return [
            'edit' => Pages\EditMyExperiment::getUrl(
                ['record' => $record->id]
            ),
        ];
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

        // Si on est sur la page d'édition et qu'on a can_configure, on permet l'accès
        if (request()->route()->getName() === 'filament.admin.resources.my-experiments.edit') {
            $experimentId = request()->route('record');
            if ($experimentId) {
                $hasConfigureAccess = Experiment::find($experimentId)
                    ->users()
                    ->where('users.id', $user->id)
                    ->where('can_configure', true)
                    ->exists();

                if ($hasConfigureAccess) {
                    return true;
                }
            }
        }

        // Sinon, on vérifie le rôle comme avant
        if ($user->hasRole('secondary_experimenter')) {
            abort(403, "En tant que compte secondaire, vous n'avez pas accès à cette section. Utilisez le tableau de bord pour gérer vos expérimentations.");
        }

        return true;
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Si on est sur la page d'édition et qu'on a can_configure, on permet l'accès
        if (request()->route()->getName() === 'filament.admin.resources.my-experiments.edit') {
            $experimentId = request()->route('record');
            if ($experimentId) {
                $hasConfigureAccess = Experiment::find($experimentId)
                    ->users()
                    ->where('users.id', $user->id)
                    ->where('can_configure', true)
                    ->exists();

                if ($hasConfigureAccess) {
                    return true;
                }
            }
        }

        // Sinon, on vérifie le rôle comme avant
        if ($user->hasRole('secondary_experimenter')) {
            abort(403, "En tant que compte secondaire, vous n'avez pas accès à cette section. Utilisez le tableau de bord pour gérer vos expérimentations.");
        }

        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::id() === $record->created_by
            || $record->users()
            ->where('users.id', Auth::id())
            ->where('can_configure', true)
            ->exists();
    }
}
