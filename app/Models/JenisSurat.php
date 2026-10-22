<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'kode', 'is_active'])]
class JenisSurat extends Model
{
    protected $table = 'jenis_surat';

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'jenis_surat_id');
    }
}
