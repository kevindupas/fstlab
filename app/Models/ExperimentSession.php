<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperimentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'experiment_id', 'participant_name', 'participant_email', 'group_data', 'actions_log', 'duration'
    ];

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }
}
