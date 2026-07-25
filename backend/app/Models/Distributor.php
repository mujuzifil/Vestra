<?php

namespace App\Models;

use App\Enums\DistributorAccountStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Distributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'distributor_request_id',
        'sales_representative_id',
        'status',
        'company_name',
        'trading_name',
        'registration_number',
        'tax_identification',
        'vat_number',
        'business_type',
        'industry',
        'years_in_business',
        'company_size',
        'website',
        'primary_contact_name',
        'email',
        'phone',
        'country',
        'district',
        'city',
        'address',
        'postal_address',
        'logo_path',
        'operating_hours_json',
        'bank_info_json',
        'billing_info_json',
        'expected_monthly_volume',
        'products_of_interest',
        'approved_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DistributorAccountStatus::class,
            'operating_hours_json' => 'array',
            'bank_info_json' => 'array',
            'billing_info_json' => 'array',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
            'years_in_business' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DistributorRequest::class, 'distributor_request_id');
    }

    public function salesRepresentative(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(DistributorBranch::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(DistributorContact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DistributorDocument::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(QuotationRequest::class);
    }

    public function paymentUploads(): HasMany
    {
        return $this->hasMany(PaymentUpload::class);
    }

    public function creditAccount(): HasOne
    {
        return $this->hasOne(CreditAccount::class);
    }

    public function negotiatedPrices(): HasMany
    {
        return $this->hasMany(DistributorProductPrice::class);
    }

    public function defaultBranch(): ?DistributorBranch
    {
        return $this->branches()->where('is_default', true)->first()
            ?? $this->branches()->first();
    }

    public function primaryContact(): ?DistributorContact
    {
        return $this->contacts()->where('is_primary', true)->first()
            ?? $this->contacts()->first();
    }

    public function isActive(): bool
    {
        return $this->status === DistributorAccountStatus::ACTIVE;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset($this->logo_path) : null;
    }
}
