<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccupationGrowth extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'occupation_growth';
}
