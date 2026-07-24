<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'full_name',
        'phone',
        'city',
        'region',
        'district',
        'address_line',
        'address_line_2',
        'postal_code',
        'country',
        'delivery_notes',
        'is_default',
        'is_default_shipping',
        'is_default_billing',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_default_shipping' => 'boolean',
            'is_default_billing' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDefaultShipping($query)
    {
        return $query->where('is_default_shipping', true);
    }

    public function scopeDefaultBilling($query)
    {
        return $query->where('is_default_billing', true);
    }

    public function setAsDefaultShipping(): void
    {
        $this->user->addresses()
            ->where('id', '!=', $this->id)
            ->where('is_default_shipping', true)
            ->update(['is_default_shipping' => false]);

        $this->is_default_shipping = true;
        $this->saveQuietly();
    }

    public function setAsDefaultBilling(): void
    {
        $this->user->addresses()
            ->where('id', '!=', $this->id)
            ->where('is_default_billing', true)
            ->update(['is_default_billing' => false]);

        $this->is_default_billing = true;
        $this->saveQuietly();
    }
}
