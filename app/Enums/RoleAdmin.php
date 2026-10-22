<?php

namespace App\Enums;

enum RoleAdmin: string
{
    case Superadmin = 'superadmin';
    case Sekretariat = 'sekretariat';
    case Unit = 'unit';

    /** Alias kompatibilitas untuk kode lama. */
    public const Admin = self::Sekretariat;

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Super Admin',
            self::Sekretariat => 'Sekretariat',
            self::Unit => 'Unit',
        };
    }
}
