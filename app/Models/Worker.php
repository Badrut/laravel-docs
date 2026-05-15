<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = ['worker_role_id', 'name', 'phone', 'address'];

    public function role()
    {
        return $this->belongsTo(WorkerRole::class, 'worker_role_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_workers', 'worker_id', 'project_id');
    }
}