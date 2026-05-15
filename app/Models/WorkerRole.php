<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerRole extends Model
{
    use HasFactory;

    protected $fillable = ['role_name', 'default_wage'];

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }
}