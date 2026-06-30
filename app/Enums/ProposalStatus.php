<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ProposalStatus: int implements HasColor, HasIcon, HasLabel
{
    case New = 0;
    case Approved = 1;
    case Rejected = 2;

    public function getLabel(): string
    {
        return __($this->name);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::New => Heroicon::OutlinedClock,
            self::Approved => Heroicon::OutlinedCheckCircle,
            self::Rejected => Heroicon::OutlinedXCircle,
        };
    }
}
