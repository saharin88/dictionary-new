<?php

namespace App\Filament\Resources\Terms\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Term Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter the term to define')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (callable $set, ?string $state) {
                                        if (! $state) {
                                            return;
                                        }
                                        // Auto-generate slug only if user hasn't manually set it
                                        $set('slug', str()->slug($state));
                                    })
                                    ->columnSpan(1),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('terms', 'slug', ignoreRecord: true)
                                    ->placeholder('auto-generated-from-title')
                                    ->helperText('URL-friendly version of the title')
                                    ->columnSpan(1),
                            ]),

                        RichEditor::make('description')
                            ->label('Full Description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->extraInputAttributes(['style' => 'min-height: 200px;'])
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                                'clearFormatting',
                            ]),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('When enabled, this term will be visible to the public')
                            ->default(true),
                    ]),
            ]);
    }
}
