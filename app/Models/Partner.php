<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    public function programs()
    {
        return $this->hasMany('App\Models\Program');
    }

    public function activePrograms()
    {
        return $this->hasMany('App\Models\Program')->where('active', 'TRUE');
    }

    // Scorecard Data
    public function accreditation()
    {
        return $this->hasOne('App\Models\Scorecard\Accreditation', 'school_id', 'school_id');
    }

    public function admissions()
    {
        return $this->hasOne('App\Models\Scorecard\Admission', 'school_id', 'school_id');
    }

    public function earnings()
    {
        return $this->hasOne('App\Models\Scorecard\Earning', 'school_id', 'school_id');
    }

    public function rank()
    {
        return $this->hasOne('App\Models\Scorecard\Rank', 'school_id', 'school_id');
    }
}
