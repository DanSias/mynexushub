<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramTrack extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function program()
    {
        return $this->belongsTo('App\Models\Program');
    }
}
