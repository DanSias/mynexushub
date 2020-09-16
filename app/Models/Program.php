<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $casts = [
        "priority" => "boolean",
    ];

    public function partner()
    {
        return $this->belongsTo('App\Models\Partner');
    }

    public function requirements()
    {
        return $this->hasOne('App\Models\ProgramRequirement');
    }
    
    public function concentrations()
    {
        return $this->hasMany('App\Models\ProgramConcentration');
    }
    
}
