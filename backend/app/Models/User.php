<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'username',
        'phone',
        'password',
        'status',
        'force_password_change_at',
        'password_changed_at',
        'locked_at',
        'department',
        'job_title',
        'employee_id',
        'notes',
        'date_of_birth',
        'gender',
        'avatar_path',
        'preferences_json',
        'is_admin',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'force_password_change_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'locked_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
            'preferences_json' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin
            || $this->hasRole('Super Administrator')
            || $this->hasRole('super-admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin()
            && $this->isActive()
            && ! $this->isLocked();
    }

    public function isDistributor(): bool
    {
        return $this->hasRole('distributor') && $this->distributor?->isActive();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function mustChangePassword(): bool
    {
        return $this->force_password_change_at !== null;
    }

    public function requirePasswordChange(): void
    {
        $this->force_password_change_at = now();
        $this->saveQuietly();
    }

    public function clearPasswordChangeRequired(): void
    {
        $this->force_password_change_at = null;
        $this->password_changed_at = now();
        $this->saveQuietly();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(self::class, 'updated_by');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(CustomerPreference::class);
    }

    public function deletionRequests(): HasMany
    {
        return $this->hasMany(CustomerDeletionRequest::class);
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class, 'customer_id');
    }

    public function customerTags(): BelongsToMany
    {
        return $this->belongsToMany(CustomerTag::class, 'customer_tag', 'customer_id', 'customer_tag_id');
    }

    public function distributor(): HasOne
    {
        return $this->hasOne(Distributor::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function loginActivities(): HasMany
    {
        return $this->hasMany(LoginActivity::class);
    }

    public function paymentTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(PaymentTransaction::class, Order::class);
    }

    public function lifetimeOrderCount(): int
    {
        return cache()->remember("user.{$this->id}.lifetime_order_count", 300, fn (): int => $this->orders()->count());
    }

    public function lifetimeSpend(): float
    {
        return cache()->remember("user.{$this->id}.lifetime_spend", 300, fn (): float => (float) $this->orders()->where('payment_status', 'paid')->sum('total_amount'));
    }

    public function recentOrders(int $limit = 5): Collection
    {
        return $this->orders()->latest()->limit($limit)->get();
    }

    public function initials(): string
    {
        $parts = explode(' ', trim($this->name));
        $first = strtoupper(substr($parts[0] ?? '', 0, 1));
        $second = strtoupper(substr($parts[1] ?? $parts[0] ?? '', 0, 1));

        return $first.$second;
    }

    public function avatarUrl(): ?string
    {
        if (empty($this->avatar_path)) {
            return null;
        }

        if (str_starts_with($this->avatar_path, 'http://') || str_starts_with($this->avatar_path, 'https://')) {
            return $this->avatar_path;
        }

        if (str_starts_with($this->avatar_path, 'avatars/') || str_starts_with($this->avatar_path, 'storage/')) {
            return asset('storage/'.ltrim(str_replace('storage/', '', $this->avatar_path), '/'));
        }

        return asset($this->avatar_path);
    }

    public function lastOrder(): ?Order
    {
        return $this->orders()->latest()->first();
    }

    public function lastOrderAt(): ?Carbon
    {
        return $this->lastOrder()?->created_at;
    }

    public function averageOrderValue(): float
    {
        $count = $this->lifetimeOrderCount();

        return $count > 0 ? $this->lifetimeSpend() / $count : 0.0;
    }

    public function largestOrder(): ?Order
    {
        return $this->orders()->orderByDesc('total_amount')->first();
    }

    public function favouriteCategory(): ?string
    {
        $category = $this->orders()
            ->with('items.product.category')
            ->get()
            ->pluck('items.*.product.category.name')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return $category ?: null;
    }

    public function favouriteProduct(): ?string
    {
        $product = $this->orders()
            ->with('items')
            ->get()
            ->pluck('items.*.product_name')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return $product ?: null;
    }

    public function customerStatusLabel(): string
    {
        return $this->isActive() ? 'Active' : 'Inactive';
    }

    public function customerStatusColor(): string
    {
        return $this->isActive() ? 'success' : 'danger';
    }

    public function scopeRegisteredBetween($query, ?string $from, ?string $until)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('created_at', '<=', $until));
    }

    public function scopeRecentlyRegistered($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->whereHas('orders', fn ($q) => $q->whereDate('created_at', '>=', now()->subDays($days)));
    }

    public function scopeHighValue($query, float $threshold = 200000)
    {
        return $query->whereHas('orders', fn ($q) => $q->selectRaw('SUM(total_amount) as total')->having('total', '>=', $threshold));
    }

    public function scopeLifetimeSpendBetween($query, ?float $min, ?float $max)
    {
        return $query->whereHas('orders', function ($q) use ($min, $max) {
            $q->selectRaw('user_id, SUM(total_amount) as total')
                ->groupBy('user_id')
                ->when($min !== null, fn ($q2) => $q2->having('total', '>=', $min))
                ->when($max !== null, fn ($q2) => $q2->having('total', '<=', $max));
        });
    }

    public function scopeLifetimeOrdersBetween($query, ?int $min, ?int $max)
    {
        return $query->whereHas('orders', function ($q) use ($min, $max) {
            $q->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->when($min !== null, fn ($q2) => $q2->having('count', '>=', $min))
                ->when($max !== null, fn ($q2) => $q2->having('count', '<=', $max));
        });
    }

    public function scopeHasOrders($query)
    {
        return $query->has('orders');
    }

    public function scopeHasNoOrders($query)
    {
        return $query->doesntHave('orders');
    }

    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function customerDocuments(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class);
    }
}
