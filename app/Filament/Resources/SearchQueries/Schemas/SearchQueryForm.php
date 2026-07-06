<?php

namespace App\Filament\Resources\SearchQueries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SearchQueryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->inlineLabel()
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->nullable()
                            ->maxLength(255),

                        TextEntry::make('search_query'),

                        TextEntry::make('count')
                            ->label('Searches'),

                        TextEntry::make('searched_at')
                            ->dateTime('Y-m-d H:i:s'),

                        IconEntry::make('has_result')
                            ->boolean(),
                    ]),
            ]);
    }
}
