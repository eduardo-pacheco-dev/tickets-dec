<?php

namespace App\Enums;

enum StationAttachmentType: string
{
    case Tssr = 'tssr';
    case Ppi = 'ppi';

    public function label(): string
    {
        return match ($this) {
            self::Tssr => 'TSSR',
            self::Ppi => 'PPI',
        };
    }
}
