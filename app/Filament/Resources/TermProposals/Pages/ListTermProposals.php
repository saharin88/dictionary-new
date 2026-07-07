<?php

namespace App\Filament\Resources\TermProposals\Pages;

use App\Filament\Resources\TermProposals\TermProposalResource;
use Filament\Resources\Pages\ListRecords;

class ListTermProposals extends ListRecords
{
    protected static string $resource = TermProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
