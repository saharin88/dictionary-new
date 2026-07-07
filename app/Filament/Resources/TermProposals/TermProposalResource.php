<?php

namespace App\Filament\Resources\TermProposals;

use App\Filament\Resources\TermProposals\Pages\ListTermProposals;
use App\Filament\Resources\TermProposals\Tables\TermProposalsTable;
use App\Models\TermProposal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TermProposalResource extends Resource
{
    protected static ?string $model = TermProposal::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return TermProposalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTermProposals::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
