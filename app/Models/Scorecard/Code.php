<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'cip_codes';

    public function bachelor()
    {
        return $this->hasOne('App\Models\Scorecard\BachelorConferral', 'cip6d', 'cip6d');
    }

    public function master()
    {
        return $this->hasOne('App\Models\Scorecard\MasterConferral', 'cip6d', 'cip6d');
    }

    public function doctorate()
    {
        return $this->hasOne('App\Models\Scorecard\DoctorateConferral', 'cip6d', 'cip6d');
    }

    public function occupations()
    {
        return $this->hasMany('App\Models\Scorecard\OccupationCode', 'cip6d', 'cip6d');
    }

    public function competition()
    {
        return $this->hasMany('App\Models\Scorecard\Competition', 'cip6d', 'cip6d');
    }

    public function keyword()
    {
        return $this->hasOne('App\Models\Scorecard\Keyword', 'cip6d', 'cip6d');
    }
    
    public function searchVolume()
    {
        return $this->hasOne('App\Models\Scorecard\SearchVolume', 'cip6d', 'cip6d');
    }
}
