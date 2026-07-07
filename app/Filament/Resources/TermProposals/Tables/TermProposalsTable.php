<?php

namespace App\Filament\Resources\TermProposals\Tables;

use App\Filament\Actions\ApplyTermProposalAction;
use App\Filament\Resources\Terms\TermResource;
use App\Models\TermProposal;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class TermProposalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('term.title')
                    ->label('Term')
                    ->url(fn (TermProposal $record): ?string => $record->term ? TermResource::getUrl('edit',
                        ['record' => $record->term]) : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->placeholder('Anonymous')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(80)
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
