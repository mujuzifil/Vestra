<?php

namespace App\Models;

use App\Enums\WorkflowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomatedWorkflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'event',
        'conditions',
        'action',
        'action_config',
        'status',
        'run_count',
        'last_run_at',
        'created_by',
    ];

    protected $casts = [
        'status' => WorkflowStatus::class,
        'conditions' => 'array',
        'action_config' => 'array',
        'run_count' => 'integer',
        'last_run_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', WorkflowStatus::ACTIVE->value);
    }

    public function recordRun(): void
    {
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
    }
}
