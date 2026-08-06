<?php

namespace App\Models;

use App\Enums\QuoteRequestPriority;
use App\Enums\QuoteRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'full_name',
        'company_name',
        'email',
        'phone',
        'district',
        'city',
        'address',
        'preferred_delivery_date',
        'delivery_location',
        'status',
        'priority',
        'estimated_value',
        'expected_close_date',
        'source',
        'ip_address',
        'user_agent',
        'requirements',
        'admin_notes',
        'assigned_to',
        'attachments',
        'crm_metadata',
        'user_id',
        'company_profile_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuoteRequestStatus::class,
            'preferred_delivery_date' => 'date',
            'expected_close_date' => 'date',
            'estimated_value' => 'decimal:2',
            'attachments' => 'array',
            'crm_metadata' => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? ucfirst((string) $this->status);
    }

    public function statusColor(): string
    {
        return $this->status?->color() ?? 'gray';
    }

    public function priorityEnum(): ?QuoteRequestPriority
    {
        return QuoteRequestPriority::tryFromMixed($this->priority);
    }

    public function priorityLabel(): string
    {
        return $this->priorityEnum()?->label() ?? (filled($this->priority) ? ucfirst((string) $this->priority) : '—');
    }

    public function priorityColor(): string
    {
        return $this->priorityEnum()?->color() ?? 'gray';
    }

    /**
     * @return array<int, array{path: string, name: string, url: string}>
     */
    public function attachmentList(): array
    {
        $attachments = $this->attachments ?? [];

        return collect($attachments)
            ->filter(fn ($path) => filled($path))
            ->values()
            ->map(fn ($path, int $index) => [
                'index' => $index,
                'path' => $path,
                'name' => basename((string) $path),
                'url' => Storage::disk('public')->url($path),
            ])
            ->toArray();
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = '%'.mb_strtolower(trim($term)).'%';

        return $query->where(function (Builder $q) use ($term): void {
            $q->whereRaw('LOWER(reference_number) LIKE ?', [$term])
                ->orWhereRaw('LOWER(company_name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(full_name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                ->orWhereRaw('LOWER(phone) LIKE ?', [$term])
                ->orWhereHas('assignedUser', function (Builder $userQuery) use ($term): void {
                    $userQuery->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                })
                ->orWhereHas('items', function (Builder $itemQuery) use ($term): void {
                    $itemQuery->whereRaw('LOWER(product_name) LIKE ?', [$term]);
                });
        });
    }

    /**
     * @param  array<int, string>  $statuses
     */
    public function scopeStatusIn(Builder $query, array $statuses): Builder
    {
        $statuses = array_values(array_filter($statuses));

        if ($statuses === []) {
            return $query;
        }

        return $query->whereIn('status', $statuses);
    }

    /**
     * @param  array<int, string>  $priorities
     */
    public function scopePriorityIn(Builder $query, array $priorities): Builder
    {
        $priorities = array_values(array_filter($priorities));

        if ($priorities === []) {
            return $query;
        }

        return $query->whereIn('priority', $priorities);
    }

    public function scopeCreatedThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
