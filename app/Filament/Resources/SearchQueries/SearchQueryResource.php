<?php

namespace App\Filament\Resources\SearchQueries;

use App\Filament\Resources\SearchQueries\Pages\EditSearchQuery;
use App\Filament\Resources\SearchQueries\Pages\ListSearchQueries;
use App\Filament\Resources\SearchQueries\Schemas\SearchQueryForm;
use App\Filament\Resources\SearchQueries\Tables\SearchQueriesTable;
use App\Filament\Traits\HasFilterableUrls;
use App\Models\SearchQuery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SearchQueryResource extends Resource
{
    use HasFilterableUrls;

    protected static ?string $model = SearchQuery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'search_query';

    public static function form(Schema $schema): Schema
    {
        return SearchQueryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SearchQueriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSearchQueries::route('/'),
            'edit' => EditSearchQuery::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
