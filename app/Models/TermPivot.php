<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TermPivot extends Pivot
{
    protected $table = 'term_term';

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (TermPivot $pivot) {
            if ($pivot->term_id === $pivot->related_term_id) {
                throw new \InvalidArgumentException(__('Cannot create a pivot relationship between the same term.'));
            }
        });

        static::created(function (TermPivot $pivot) {
            static::withoutEvents(function () use ($pivot) {
                static::firstOrCreate([
                    'term_id' => $pivot->related_term_id,
                    'related_term_id' => $pivot->term_id,
                ]);
            });
        });

        static::deleted(function (TermPivot $pivot) {
            static::withoutEvents(function () use ($pivot) {
                static::where([
                    'term_id' => $pivot->related_term_id,
                    'related_term_id' => $pivot->term_id,
                ])->delete();
            });
        });
    }
}
