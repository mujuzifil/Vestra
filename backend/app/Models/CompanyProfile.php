<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'industry',
        'business_type',
        'tax_identification',
        'registration_number',
        'website',
        'district',
        'city',
        'country',
        'address',
        'primary_contact_name',
        'primary_contact_phone',
        'primary_contact_email',
        'status',
        'account_manager_id',
        'region',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class, 'company_profile_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(CustomerFeedback::class, 'user_id', 'user_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'user_id', 'user_id');
    }

    public function customerDocuments(): HasMany
    {
        return $this->hasMany(CustomerDocument::class, 'user_id', 'user_id');
    }

    public function scopeStatusIn(Builder $query, array $statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeWithOpenQuotes(Builder $query): Builder
    {
        return $query->whereHas('quoteRequests', function (Builder $q): void {
            $q->whereNotIn('status', ['closed', 'declined', 'approved']);
        });
    }

    public function scopeWithActiveTickets(Builder $query): Builder
    {
        return $query->whereHas('supportTickets', function (Builder $q): void {
            $q->whereNotIn('status', ['resolved', 'closed']);
        });
    }

    public function scopeWithDistributor(Builder $query): Builder
    {
        return $query->whereHas('user.distributor');
    }

    public function scopeCreatedThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = '%'.mb_strtolower($term).'%';

        return $query->where(function (Builder $q) use ($term): void {
            $q->whereRaw('LOWER(company_name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(primary_contact_name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(primary_contact_email) LIKE ?', [$term])
                ->orWhereRaw('LOWER(primary_contact_phone) LIKE ?', [$term])
                ->orWhereRaw('LOWER(tax_identification) LIKE ?', [$term])
                ->orWhereRaw('LOWER(registration_number) LIKE ?', [$term])
                ->orWhereRaw('LOWER(country) LIKE ?', [$term])
                ->orWhereRaw('LOWER(region) LIKE ?', [$term])
                ->orWhereRaw('LOWER(city) LIKE ?', [$term])
                ->orWhereRaw('LOWER(district) LIKE ?', [$term]);
        });
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? ucfirst((string) $this->status);
    }

    public function statusColor(): string
    {
        return $this->status?->color() ?? 'gray';
    }
}
