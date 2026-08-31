<?php

namespace App\Filament\Resources\Terms\RelationManagers;

use App\Filament\Resources\Terms\TermResource;
use App\Models\Term;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RelatedTermsRelationManager extends RelationManager
{
    protected static string $relationship = 'relatedTerms';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->inverseRelationship('relatedTerms')
            ->columns([
                TextColumn::make('title')
                    ->url(fn (Term $record): string => TermResource::getUrl('edit', ['record' => $record]))
                    ->searchable()
                    ->sortable()
                    ->color('primary'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelect(
                        fn (Select $select) => $select->optionsLimit(20),
                    )
                    ->recordSelectOptionsQuery(
                        fn (Builder $query): Builder => $query->whereKeyNot($this->getOwnerRecord()->getKey()),
                    ),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
