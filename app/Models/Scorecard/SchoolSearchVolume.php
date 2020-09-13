<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSearchVolume extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'school_search_volume';
}
