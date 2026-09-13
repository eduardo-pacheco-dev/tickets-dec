<?php

namespace App\Enums;

enum StationAttachmentType: string
{
    case Tssr = 'tssr';
    case Ppi = 'ppi';
    case DocD = 'doc_d';

    public function label(): string
    {
        return match ($this) {
            self::Tssr => 'TSSR',
            self::Ppi => 'PPI',
            self::DocD => 'DOC-D',
        };
    }
}
