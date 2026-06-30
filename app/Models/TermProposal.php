<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Database\Factories\TermProposalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['term_id', 'email', 'description', 'status'])]
class TermProposal extends Model
{
    /** @use HasFactory<TermProposalFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => ProposalStatus::New->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'created_at' => 'immutable_datetime',
        ];
    }

    /**
     * Get the term this proposal belongs to.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
