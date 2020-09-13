<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessCaseNote extends Model
{
    use HasFactory;

    public function case()
    {
        $this->belongsTo('App\Models\BusinessCase');
    }
}
