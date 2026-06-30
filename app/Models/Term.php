<?php

namespace App\Models;

use Database\Factories\TermFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'description', 'is_published'])]
class Term extends Model
{
    /** @use HasFactory<TermFactory> */
    use HasFactory;

    protected $attributes = [
        'is_published' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Automatically generate slug from title when saving
        static::saving(function (self $term) {
            if (empty($term->slug)) {
                $term->slug = Str::slug($term->title);
            }
        });
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(TermProposal::class);
    }

    public function searchQueries()
    {
        return $this->belongsToMany(SearchQuery::class)
            ->using(SearchQueryTerm::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
