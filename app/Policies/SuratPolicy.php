<?php

namespace App\Policies;

use App\Enums\StatusSurat;
use App\Models\Surat;
use App\Models\User;

class SuratPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Surat $surat): bool
    {
        return $user->is_active && ($user->dapatKelolaSemuaSurat() || $surat->created_by === $user->getKey());
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function download(User $user, Surat $surat): bool
    {
        return $user->is_active && ($user->dapatKelolaSemuaSurat() || $surat->created_by === $user->getKey());
    }

    public function publish(User $user, Surat $surat): bool
    {
        return $user->is_active
            && ($user->dapatKelolaSemuaSurat() || $surat->created_by === $user->getKey())
            && $surat->status === StatusSurat::Draf;
    }

    /** Semua perubahan ke status ditarik wajib menyertakan alasan. */
    public function revoke(User $user, Surat $surat): bool
    {
        return $user->is_active
            && ($user->dapatKelolaSemuaSurat() || $surat->created_by === $user->getKey())
            && $surat->status !== StatusSurat::Ditarik;
    }
}
