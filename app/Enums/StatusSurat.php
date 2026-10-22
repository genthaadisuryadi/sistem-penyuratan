<?php

namespace App\Enums;

enum StatusSurat: string
{
    case Draf = 'draf';
    case Publish = 'publish';
    case Ditarik = 'ditarik';

    /** Alias kompatibilitas untuk kode lama. */
    public const Aktif = self::Publish;
    public const Dicabut = self::Ditarik;

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Publish => 'Publish',
            self::Ditarik => 'Ditarik',
        };
    }
}
