<?php

namespace App\Filament\Resources\Terms\RelationManagers;

use App\Filament\Actions\ApplyTermProposalAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class TermProposalsRelationManager extends RelationManager
{
    protected static string $relationship = 'proposals';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('email')
                    ->placeholder('Anonymous')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->limit(100)
                    ->formatStateUsing(fn (string $state): string => strip_tags($state))
                    ->tooltip(fn (TextColumn $column): Htmlable => new HtmlString($column->getState())),

                TextColumn::make('created_at')
                    ->alignCenter()
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ApplyTermProposalAction::make(),
            ]);
    }
}
