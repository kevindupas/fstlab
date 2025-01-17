<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperimentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'experiment_id',
        'experiment_link_id',
        'participant_number',
        'group_data',
        'actions_log',
        'duration',
        'started_at',
        'completed_at',
        'status',
        'browser',
        'device_type',
        'operating_system',
        'screen_width',
        'screen_height',
        'notes',
        'feedback',
        'errors_log',
        'is_dark'
    ];

    protected $casts = [
        'group_data' => 'array',
        'actions_log' => 'array',
        'errors_log' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }

    public function experimentLink()
    {
        return $this->belongsTo(ExperimentLink::class);
    }

    public function accessRequests()
    {
        return $this->hasMany(ExperimentAccessRequest::class, 'experiment_id', 'experiment_id');
    }
}
