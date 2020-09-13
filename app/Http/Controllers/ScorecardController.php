<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Scorecard\Code;
use App\Models\Scorecard\Occupation;
use App\Models\Scorecard\School;

class ScorecardController extends Controller
{
    public function codes()
    {
        return Code::all(['cip_number', 'cip6d']);
    }

    public function code($key)
    {
        $check = (is_numeric($key)) ? 'cip_number' : 'cip6d';
        return Code::where($check, $key)
            ->with('bachelor')
            ->with('master')
            ->with('doctorate')
            ->with('occupations:cip6d,occupation')
            ->with('competition')
            ->with('keyword')
            ->with('searchVolume')
            ->first();
    }

    public function occupations()
    {
        return Occupation::all();
    }

    public function occupation($key)
    {
        return Occupation::where('occupation', $key)
            ->with('growth')
            ->with('growthRate')
            ->first();
    }

    public function schools()
    {
        return School::all();
    }

    // Single school with relationship data
    public function school($key)
    {
        $check = (is_numeric($key)) ? 'school_id' : 'school_name';
        return School::where($check, $key)
            ->with('accreditation:school_id,abbreviation,agency_name')
            ->with('admission')
            ->with('enrollment')
            ->with('searchVolume')
            ->with('rank')
            ->with('team')
            ->with('totalConferrals')
            ->first();
    }
}
