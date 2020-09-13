<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BachelorConferral extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'conferrals_bachelor';
}
