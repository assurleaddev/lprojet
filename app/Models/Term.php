<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasFeaturedImage;
use App\Concerns\HasUniqueSlug;
use App\Concerns\QueryBuilderTrait;
use App\Concerns\HasMedia;
use App\Observers\TermObserver;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy([TermObserver::class])]
class Term extends Model implements SpatieHasMedia
{
    use HasFactory;
    use HasFeaturedImage;
    use HasUniqueSlug;
    use QueryBuilderTrait;
    use HasMedia;

    protected $fillable = [
        'name',
        'slug',
        'taxonomy',
        'description',
        'parent_id',
        'count',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug($model);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug($model);
            }
        });
    }

    /**
     * Get the taxonomy model that owns the term.
     */
    public function taxonomyModel(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy', 'name');
    }

    /**
     * Get the parent term.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    /**
     * Get the child terms.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Term::class, 'parent_id');
    }

    /**
     * The posts that belong to the term.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'term_relationships');
    }

    /**
     * Custom sort method for post_count (alias for posts_count)
     */
    public function sortByPostCount(Builder $query, string $direction = 'asc'): void
    {
        $query->withCount('posts')->orderBy('posts_count', $direction);
    }

    /**
     * Custom sort method for posts_count
     */
    public function sortByPostsCount(Builder $query, string $direction = 'asc'): void
    {
        $query->withCount('posts')->orderBy('posts_count', $direction);
    }

    /**
     * Register media collections for terms
     */
    public function registerMediaCollections(): void
    {
        $this->addFeaturedImageCollection();
    }

    /**
     * Register media conversions for terms
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerFeaturedImageConversions();
    }
}
