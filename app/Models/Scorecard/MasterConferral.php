<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterConferral extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'conferrals_master';
}
