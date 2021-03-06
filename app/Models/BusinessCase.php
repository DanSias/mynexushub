<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessCase extends Model
{
    use HasFactory;

    public function program()
    {
        $this->belongsTo('App\Models\Program');
    }

    public function notes()
    {
        $this->hasMany('App\Models\BusinessCaseNote');
    }
}
