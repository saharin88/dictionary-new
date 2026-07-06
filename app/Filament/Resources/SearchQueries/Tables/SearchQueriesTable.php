<?php

namespace App\Filament\Resources\SearchQueries\Tables;

use App\Filament\Resources\Terms\TermResource;
use App\Models\SearchQuery;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SearchQueriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('search_query')
                    ->label('Search Query')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('count')
                    ->label('Searches')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('terms_count')
                    ->alignCenter()
                    ->label('Terms')
                    ->counts('terms')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : '-')
                    ->url(fn ($state, SearchQuery $record): ?string => $state > 0 ? TermResource::getFilteredIndexUrl([
                        'searchQueries' => [$record->getKey()],
                    ]) : null)
                    ->color(fn ($state) => $state > 0 ? 'primary' : null)
                    ->sortable(),
                IconColumn::make('has_result')
                    ->alignCenter()
                    ->boolean()
                    ->sortable(),
                TextColumn::make('searched_at')
                    ->alignCenter()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->alignCenter()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->alignCenter()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('has_result')
                    ->label('Result')
                    ->options([
                        true => 'With result',
                        false => 'Without result',
                    ]),
                TernaryFilter::make('has_terms')
                    ->label('Terms')
                    ->placeholder('All')
                    ->trueLabel('Has Terms')
                    ->falseLabel('No Terms')
                    ->queries(
                        true: fn (Builder $query) => $query->has('terms'),
                        false: fn (Builder $query) => $query->doesntHave('terms'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
