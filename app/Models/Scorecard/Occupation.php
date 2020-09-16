<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'occupation_outlook';

    public function growth()
    {
        return $this->hasMany('App\Models\Scorecard\OccupationGrowth', 'occupation', 'occupation');
    }

    public function growthRate()
    {
        return $this->hasOne('App\Models\Scorecard\OccupationGrowthRate', 'occupation', 'occupation');
    }

    public function rank()
    {
        return $this->hasOne('App\Models\Scorecard\OccupationRank', 'occupation', 'occupation');
    }
}
