<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConferralTotal extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'conferral_totals';
}
