<?php

namespace App\Filament\Resources\Terms\Tables;

use App\Filament\Exports\TermExporter;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class TermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->formatStateUsing(fn (string $state): string => strip_tags($state))
                    ->tooltip(fn (TextColumn $column): Htmlable => new HtmlString($column->getState())),

                TextColumn::make('searchQueries.search_query')
                    ->limitList(1)
                    ->expandableLimitedList()
                    ->listWithLineBreaks()
                    ->toggleable(),

                ToggleColumn::make('is_published')
                    ->alignCenter()
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
                SelectFilter::make('is_published')
                    ->options([
                        true => 'Published',
                        false => 'Unpublished',
                    ]),

                SelectFilter::make('searchQueries')
                    ->preload()
                    ->relationship('searchQueries', 'search_query', fn (Builder $query): Builder => $query->whereHas('terms'))
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('duplicates')
                    ->label('Duplicates')
                    ->options([
                        'title' => 'By title',
                        'title_description' => 'By title and description',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $mode = $data['value'] ?? null;
                        $columns = match ($mode) {
                            'title' => ['title'],
                            'title_description' => ['title', 'description'],
                            default => [],
                        };

                        if ($columns === []) {
                            return $query;
                        }

                        $table = $query->getModel()->getTable();
                        $duplicatesSubQuery = $query->getModel()::query()
                            ->select($columns)
                            ->groupBy($columns)
                            ->havingRaw('COUNT(*) > 1');

                        return $query->whereExists(function (\Illuminate\Database\Query\Builder $subQuery) use (
                            $columns,
                            $duplicatesSubQuery,
                            $table
                        ): void {
                            $subQuery->selectRaw('1')
                                ->fromSub($duplicatesSubQuery->toBase(), 'duplicates');

                            foreach ($columns as $column) {
                                $subQuery->whereColumn("duplicates.{$column}", "{$table}.{$column}");
                            }
                        });
                    })
                    ->baseQuery(function (Builder $query, array $data, $livewire): Builder {
                        $isDuplicatesFilterActive = filled($data['value'] ?? null);

                        if ($isDuplicatesFilterActive && blank($livewire->getTableSortColumn())) {
                            $query
                                ->reorder($query->qualifyColumn('title'))
                                ->orderBy($query->getModel()->getQualifiedKeyName());
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->successNotificationTitle('Selected terms published')
                        ->action(fn (Collection $records): int => $records->toQuery()->update(['is_published' => true])),
                    BulkAction::make('unpublish')
                        ->label('Unpublish selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->successNotificationTitle('Selected terms unpublished')
                        ->action(fn (Collection $records
                        ): int => $records->toQuery()->update(['is_published' => false])),
                    ExportBulkAction::make()
                        ->exporter(TermExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
