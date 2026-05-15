<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    use HasFactory;

    // INI KUNCI JAWABANNYA BANG! Wajib ada biar gak NULL
    protected $fillable = [
        'project_id',
        'file_path' 
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}