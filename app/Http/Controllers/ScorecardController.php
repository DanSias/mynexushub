<?php

namespace App\Http\Controllers;

use DB;
use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Models\Scorecard\Code;
use App\Models\Scorecard\Occupation;
use App\Models\Scorecard\School;
use App\Models\Scorecard\Conferral;

class ScorecardController extends Controller
{
    public function index()
    {
        return Inertia::render('Scorecard');
    }

    public function saves()
    {
        return \App\Models\Scorecard\Save::orderBy('updated_at', 'desc')->get();
    }
    
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

    // JSON Data

    public function keywords()
    {
        return \App\Models\Scorecard\Keyword::all();
    }

    public function conferralCodes($level = '')
    {
        if ($level != '') {
            $codes = Conferral::select('cip6d', DB::raw('sum(conferrals) as conferrals'))
                ->where('level', ucFirst($level))
                ->orderBy('conferrals', 'desc')
                ->groupBy('cip6d')
                ->get();
        } else {
            $codes = Conferral::select('cip6d', DB::raw('sum(conferrals) as conferrals'))
                ->orderBy('conferrals', 'desc')
                ->groupBy('cip6d')
                ->get();
        }
        // Select input expects term property
        foreach ($codes as $code) {
            $code->term = $code->cip6d;
        }
        return $codes;
    }
    
    public function allCodes()
    {
        $codes = $this->codes();
        // Select input expects term property
        foreach ($codes as $code) {
            $code->term = $code->cip6d;
        }
        return $codes;
    }

    
    public function degreeOccupations($degree = '')
    {
        if ($degree == '') {
            return [];
        }

        $occupations = \App\Models\Scorecard\OccupationCode::where('cip6d', $degree)
            ->leftJoin('occupation_growth_rate', 'degree_occupations.occupation', '=', 'occupation_growth_rate.occupation')
            ->orderBy('occupation_growth_rate.employment_2018', 'desc')
            ->pluck('degree_occupations.occupation')
            ->toArray();
        return $occupations;
    }

    public function allOccupations()
    {
        return Occupation::all()->pluck('occupation')->toArray();
    }

    public function allSchools()
    {
        return School::all()->pluck('school_name')->toArray();
    }

    public function programAccreditation($code)
    {
        return \App\Models\Scorecard\ProgramAccreditation::where('accreditation', '!=', 'None')
            ->where('cip6d', $code)
            ->get(['school_name', 'level', 'cip6d', 'accreditation']);
    }
}
