<?php

namespace App\Enums;

enum Locale: string
{
    case PtBr = 'pt_BR';
    case En = 'en';

    public function label(): string
    {
        return match ($this) {
            self::PtBr => 'Português (Brasil)',
            self::En => 'English',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::PtBr => 'PT',
            self::En => 'EN',
        };
    }
}
