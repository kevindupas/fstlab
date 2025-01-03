<?php

namespace App\Filament\Resources\ExperimentSessionResource\Pages;

use App\Filament\Resources\ExperimentSessionResource;
use App\Models\Experiment;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListExperimentSessions extends ListRecords
{
    protected static string $resource = ExperimentSessionResource::class;
    protected static string $view = 'filament.resources.experiment-sessions.pages.list-sessions';

    public function table(Table $table): Table
    {
        $experimentId = request()->query('record');
        $tab = request()->query('tab');
        $search = request()->query('search');
        $experiment = Experiment::find($experimentId);

        // Par défaut, on met à false
        $hasCollaboratorAccess = false;

        // On vérifie l'accès collaborateur uniquement si l'expérience existe
        if ($experiment) {
            $hasCollaboratorAccess = $experiment->accessRequests()
                ->where('user_id', Auth::id())
                ->where('type', 'access')
                ->where('status', 'approved')
                ->exists();
        }

        return parent::table($table)
            ->modifyQueryUsing(function (Builder $query) use ($experimentId, $tab, $experiment, $search, $hasCollaboratorAccess) {
                if ($experimentId) {
                    $query->where('experiment_id', $experimentId);

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('group_data', 'like', "%{$search}%")
                                ->orWhere('feedback', 'like', "%{$search}%");
                        });
                    }

                    // On vérifie que l'expérience existe avant d'utiliser ses propriétés
                    if ($experiment) {
                        switch ($tab) {
                            case 'creator':
                                $query->whereHas('experimentLink', function ($q) use ($experiment) {
                                    $q->where('is_creator', true)
                                        ->orWhere('is_secondary', true);
                                });
                                break;
                            case 'mine':
                                if (!$hasCollaboratorAccess) {
                                    $query->where('id', 0);
                                } else {
                                    $query->whereHas('experimentLink', function ($q) {
                                        $q->where('user_id', Auth::id());
                                    });
                                }
                                break;
                            case 'collaborators':
                                $query->whereHas('experimentLink', function ($q) use ($experiment) {
                                    $q->whereHas('user', function ($userQ) use ($experiment) {
                                        $userQ->where(function ($sq) use ($experiment) {
                                            $sq->where('id', '!=', $experiment->created_by)
                                                ->where(function ($innerQ) use ($experiment) {
                                                    $innerQ->where('created_by', '!=', $experiment->created_by)
                                                        ->orWhereNull('created_by');
                                                });
                                        });
                                    })->where('user_id', '!=', Auth::id());
                                });
                                break;
                        }
                    }
                }

                return $query;
            });
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
