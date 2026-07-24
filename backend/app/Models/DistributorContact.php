<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'name',
        'role',
        'phone',
        'email',
        'permissions_json',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'permissions_json' => 'array',
            'is_primary' => 'boolean',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function setAsPrimary(): void
    {
        $this->distributor->contacts()
            ->where('id', '!=', $this->id)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        $this->is_primary = true;
        $this->saveQuietly();
    }
}
