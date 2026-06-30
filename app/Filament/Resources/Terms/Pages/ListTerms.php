<?php

namespace App\Filament\Resources\Terms\Pages;

use App\Filament\Exports\TermExporter;
use App\Filament\Imports\TermImporter;
use App\Filament\Resources\Terms\TermResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListTerms extends ListRecords
{
    protected static string $resource = TermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(TermExporter::class),
            ImportAction::make()
                ->importer(TermImporter::class),
        ];
    }
}
