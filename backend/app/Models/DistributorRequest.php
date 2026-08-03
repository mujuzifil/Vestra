<?php

namespace App\Models;

use App\Enums\DistributorStatus;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'business_type',
        'years_in_operation',
        'contact_person',
        'email',
        'phone',
        'address',
        'country',
        'region',
        'business_description',
        'products_interested_in',
        'target_region',
        'estimated_volume',
        'existing_customer',
        'previous_applications',
        'status',
        'priority',
        'assigned_to',
        'internal_notes',
        'documents',
    ];

    protected function casts(): array
    {
        return [
            'status' => DistributorStatus::class,
            'existing_customer' => 'boolean',
            'previous_applications' => 'integer',
            'years_in_operation' => 'integer',
            'documents' => 'array',
        ];
    }

    public function assignedAdministrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', DistributorStatus::PENDING);
    }

    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('status', DistributorStatus::UNDER_REVIEW);
    }

    public function scopeInformationRequested(Builder $query): Builder
    {
        return $query->where('status', DistributorStatus::INFORMATION_REQUESTED);
    }

    public function scopeAwaitingReview(Builder $query): Builder
    {
        return $query->whereIn('status', [DistributorStatus::PENDING->value, DistributorStatus::UNDER_REVIEW->value, DistributorStatus::INFORMATION_REQUESTED->value]);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', DistributorStatus::APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', DistributorStatus::REJECTED);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    public function scopeByRegion(Builder $query, string $region): Builder
    {
        return $query->where('region', $region);
    }

    public function scopeRecentlySubmitted(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeRecentlyUpdated(Builder $query, int $days = 7): Builder
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function statusColor(): string
    {
        return $this->status->color();
    }

    public function priorityLabel(): string
    {
        return Priority::tryFrom($this->priority)?->label() ?? ucfirst($this->priority);
    }

    public function priorityColor(): string
    {
        return Priority::tryFrom($this->priority)?->color() ?? 'gray';
    }

    public function isExistingCustomer(): bool
    {
        return $this->existing_customer;
    }

    public function formattedAddress(): string
    {
        $parts = array_filter([$this->address, $this->region, $this->country]);
        return implode(', ', $parts) ?: '—';
    }
}
