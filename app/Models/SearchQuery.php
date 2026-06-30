<?php

namespace App\Models;

use Database\Factories\SearchQueryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['search_query', 'title', 'count', 'searched_at', 'has_result'])]
class SearchQuery extends Model
{
    /** @use HasFactory<SearchQueryFactory> */
    use HasFactory;

    protected $attributes = [
        'count' => 1,
        'has_result' => false,
    ];

    public function terms()
    {
        return $this->belongsToMany(Term::class)
            ->using(SearchQueryTerm::class);
    }
}
