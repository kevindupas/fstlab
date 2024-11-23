<?php

// ExperimentAccessRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExperimentAccessRequest extends Model
{
    protected $fillable = [
        'user_id',
        'experiment_id',
        'type',
        'status',
        'request_message',
        'response_message',
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
