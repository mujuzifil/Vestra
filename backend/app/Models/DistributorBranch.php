<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'name',
        'manager_name',
        'phone',
        'email',
        'country',
        'district',
        'city',
        'address',
        'latitude',
        'longitude',
        'delivery_notes',
        'status',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function setAsDefault(): void
    {
        $this->distributor->branches()
            ->where('id', '!=', $this->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $this->is_default = true;
        $this->saveQuietly();
    }

    public function formattedAddress(): string
    {
        $parts = array_filter([$this->address, $this->city, $this->district, $this->country]);
        return implode(', ', $parts) ?: '—';
    }
}
