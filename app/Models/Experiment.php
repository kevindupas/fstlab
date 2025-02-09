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
        'original_creator_id',
        'doi',
        'instruction',
        'documents',
        'howitwork_page',
        'is_public',
        'responsible_institution',
    ];

    protected $casts = [
        'media' => 'array',
        'documents' => 'array',
        'howitwork_page' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('can_configure', 'can_pass');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function originalCreator()
    {
        return $this->belongsTo(User::class, 'original_creator_id');
    }

    public function sessions()
    {
        return $this->hasMany(ExperimentSession::class);
    }

    public function accessRequests()
    {
        return $this->hasMany(ExperimentAccessRequest::class);
    }

    public function links()
    {
        return $this->hasMany(ExperimentLink::class);
    }

    public function access_requests_count(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExperimentAccessRequest::class);
    }

    public function shared_links_count(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExperimentLink::class)
            ->where('user_id', '!=', $this->created_by);
    }
}
