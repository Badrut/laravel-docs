<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KopSurat extends Model
{
    use HasFactory;

    protected $table = 'kop_surats';

    protected $fillable = ['name', 'type', 'image_path'];

    /**
     * Scope: hanya kop surat sekolah
     */
    public function scopeSekolah($query)
    {
        return $query->where('type', 'sekolah');
    }

    /**
     * Scope: hanya kop surat kontraktor
     */
    public function scopeKontraktor($query)
    {
        return $query->where('type', 'kontraktor');
    }
}
