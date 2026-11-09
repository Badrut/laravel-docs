<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = ['company_name', 'person_in_charge', 'address', 'phone'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
