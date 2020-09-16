<?php

namespace App\Models\Scorecard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramAccreditation extends Model
{
    use HasFactory;

    protected $connection= 'scorecard';

    protected $table = 'programmatic_accreditation';
}
