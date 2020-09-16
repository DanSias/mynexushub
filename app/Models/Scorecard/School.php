<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    public function accreditation()
    {
        return $this->hasOne('App\Models\Scorecard\Accreditation', 'school_id', 'school_id');
    }

    public function admission()
    {
        return $this->hasOne('App\Models\Scorecard\Admission', 'school_id', 'school_id');
    }

    public function enrollment()
    {
        return $this->hasOne('App\Models\Scorecard\Enrollment', 'school_id', 'school_id');
    }

    public function team()
    {
        return $this->hasOne('App\Models\Scorecard\Team', 'school_id', 'school_id');
    }

    public function totalConferrals()
    {
        return $this->hasOne('App\Models\Scorecard\ConferralTotal', 'school_name', 'school_name');
    }

    public function searchVolume()
    {
        return $this->hasOne('App\Models\Scorecard\SchoolSearchVolume', 'school_id', 'school_id');
    }

    public function rank()
    {
        return $this->hasOne('App\Models\Scorecard\Rank', 'school_id', 'school_id');
    }
}
