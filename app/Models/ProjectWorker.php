<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectWorker extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'project_id',
        'worker_id',
    ];

    /**
     * Relasi balik ke Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke Master Worker
     * Biar nanti gampang manggil nama dan role si pekerja
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}