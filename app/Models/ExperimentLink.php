<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ExperimentLink extends Model
{
    protected $fillable = [
        'experiment_id',
        'user_id',
        'link',
        'status',
        'is_creator',
        'is_secondary',
        'is_collaborator'
    ];

    protected $casts = [
        'is_creator' => 'boolean',
        'is_secondary' => 'boolean',
        'is_collaborator' => 'boolean'
    ];

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
