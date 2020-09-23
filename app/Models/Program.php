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

    public function forecasts()
    {
        return $this->hasOne('App\Models\Forecast');
    }

    public function home()
    {
        return $this->hasOne('App\Models\LandingPage', 'program', 'code')->where('initiative', 'home');
    }

    public function enterprise()
    {
        return $this->hasOne('App\Models\ProgramEnterprise');
    }
    
    public function tracks()
    {
        return $this->hasMany('App\Models\ProgramTrack');
    }

    public function concentrations()
    {
        return $this->hasMany('App\Models\ProgramConcentration');
    }

    public function codes()
    {
        return $this->hasMany('App\Models\ProgramCode');
    }
    
}
