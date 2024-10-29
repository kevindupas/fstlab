<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use App\Models\ExperimentSession;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ExperimentSessionDetails extends Page
{
    protected static string $resource = ExperimentResource::class;

    protected static string $view = 'filament.resources.experiment-resource.pages.experiment-session-details';

    public $session;

    public function mount($record)
    {
        $this->session = ExperimentSession::with('experiment')->findOrFail($record);
        /** @var \App\Models\User */
        $user = Auth::user();
        if (!$user->can('view', $this->session)) {
            abort(403, 'Vous n\'êtes pas autorisé à voir les détails de cette session.');
        }
    }

    public function sessionInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->session)
            ->schema([
                Section::make('Informations du participant')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('participant_name')
                                    ->label('Nom')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('participant_email')
                                    ->label('Email')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('created_at')
                                    ->label('Date de participation')
                                    ->dateTime('d/m/Y H:i')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('duration')
                                    ->label('Durée')
                                    ->formatStateUsing(fn($state) => number_format($state / 1000, 2) . ' secondes')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),
            ]);
    }

    protected function getViewData(): array
    {
        return [
            'session' => $this->session,
            'groups' => json_decode($this->session->group_data),
            'actionsLog' => collect(json_decode($this->session->actions_log))->map(function ($action) {
                return [
                    'id' => $action->id,
                    'x' => $action->x,
                    'y' => $action->y,
                    'time' => \Carbon\Carbon::createFromTimestampMs($action->time)->format('H:i:s.v'),
                ];
            })
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString("Détails de la session - {$this->session->participant_name}");
    }
}
