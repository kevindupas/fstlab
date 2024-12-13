<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kenepa\ResourceLock\Models\Concerns\HasLocks;

class Experiment extends Model
{
    use HasFactory, HasLocks;

    protected $fillable = [
        'name',
        'description',
        'type',
        'media',
        'button_size',
        'button_color',
        'created_by',
        'status',
        'link',
        'doi',
        'instruction',
        'documents',
    ];

    protected $casts = [
        'media' => 'array',
        'documents' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('can_configure', 'can_pass');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(ExperimentSession::class);
    }

    public function accessRequests()
    {
        return $this->hasMany(ExperimentAccessRequest::class);
    }
}
