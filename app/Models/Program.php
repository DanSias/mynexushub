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
        $this->belongsTo('App\Models\Partner');
    }
    
    public function concentrations()
    {
        $this->hasMany('App\Models\ProgramConcentration');
    }
}
