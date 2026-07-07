<?php

namespace App\Models;

use Database\Factories\TermProposalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['term_id', 'email', 'description'])]
class TermProposal extends Model
{
    /** @use HasFactory<TermProposalFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
