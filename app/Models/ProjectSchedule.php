<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'working_date'
    ];

    protected $casts = [
        'working_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}