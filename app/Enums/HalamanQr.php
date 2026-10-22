<?php

namespace App\Enums;

enum HalamanQr: string
{
    case Pertama = 'pertama';
    case Terakhir = 'terakhir';

    public function label(): string
    {
        return match ($this) {
            self::Pertama => 'Halaman pertama',
            self::Terakhir => 'Halaman terakhir',
        };
    }
}
