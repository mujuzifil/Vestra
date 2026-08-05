<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'parent_id' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products(): HasMany
    {
        // products table has no sort_order column — order by name for stable listings
        return $this->hasMany(Product::class)->orderBy('name');
    }

    public function productsCount(): int
    {
        return $this->products()->count();
    }

    public function activeProductsCount(): int
    {
        return $this->products()
            ->where('status', 'active')
            ->count();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * @return Collection<int, Category>
     */
    public function ancestorChain(): Collection
    {
        $chain = collect();
        $current = $this->parent;
        $guard = 0;

        while ($current !== null && $guard < 50) {
            $chain->prepend($current);
            $current = $current->parent;
            $guard++;
        }

        return $chain;
    }

    public function breadcrumbPath(): string
    {
        $names = $this->ancestorChain()->pluck('name')->all();
        $names[] = $this->name;

        return implode(' › ', $names);
    }
}
