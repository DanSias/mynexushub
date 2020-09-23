<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramEnterprise extends Model
{
    use HasFactory;

    public function program()
    {
        return $this->belongsTo('App\Models\Program');
    }
}
