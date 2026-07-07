<?php

namespace App\Filament\Actions;

use App\Models\TermProposal;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class ApplyTermProposalAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'apply';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Apply')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->fillForm(fn (TermProposal $record): array => [
                'description' => $record->description,
            ])
            ->schema([
                TextEntry::make('term.description')
                    ->label('Current translation')
                    ->html(),
                RichEditor::make('description')
                    ->label('Proposed translation')
                    ->required()
                    ->maxLength(65535)
                    ->extraInputAttributes(['style' => 'min-height: 200px;']),
            ])
            ->action(function (array $data, TermProposal $record, Component $livewire): void {
                $record->term()->update([
                    'description' => $data['description'],
                ]);

                $record->delete();

                $livewire->dispatch('term-description-updated');
            })
            ->modalHeading('Apply Term Proposal')
            ->successNotificationTitle('Proposal applied');
    }
}
