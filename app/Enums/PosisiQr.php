<?php

namespace App\Enums;

enum PosisiQr: string
{
    case KiriBawah = 'kiri-bawah';
    case TengahBawah = 'tengah-bawah';
    case KananBawah = 'kanan-bawah';
    case KiriAtas = 'kiri-atas';
    case KananAtas = 'kanan-atas';

    public function label(): string
    {
        return match ($this) {
            self::KiriBawah => 'Kiri bawah',
            self::TengahBawah => 'Tengah bawah',
            self::KananBawah => 'Kanan bawah',
            self::KiriAtas => 'Kiri atas',
            self::KananAtas => 'Kanan atas',
        };
    }

    /** Posisi horizontal: 0 = kiri, 1 = tengah, 2 = kanan. */
    public function kolom(): int
    {
        return match ($this) {
            self::KiriBawah, self::KiriAtas => 0,
            self::TengahBawah => 1,
            self::KananBawah, self::KananAtas => 2,
        };
    }

    public function atas(): bool
    {
        return in_array($this, [self::KiriAtas, self::KananAtas], true);
    }

    /** Frasa untuk kalimat, mis. "pojok kanan bawah". */
    public function frasa(): string
    {
        return match ($this) {
            self::KiriBawah => 'pojok kiri bawah',
            self::TengahBawah => 'tengah bagian bawah',
            self::KananBawah => 'pojok kanan bawah',
            self::KiriAtas => 'pojok kiri atas',
            self::KananAtas => 'pojok kanan atas',
        };
    }
}
