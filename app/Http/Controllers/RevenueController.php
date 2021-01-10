<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Revenue;
use App\Models\ProgramCode;

class RevenueController extends Controller
{
    // Single Program
    public function program($program)
    {
        $time = new Time;
        $fourYears = $time->getYear() - 3;
        $revenue = Revenue::where('program', $program)
            ->where('year', '>=', $fourYears)
            ->where('plan_starts', '>', 0)
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc')
            ->get(['program', 'year', 'term', 'starts', 'plan_starts', 'students', 'plan_students', 'revenue', 'plan_revenue']);
        // Uppercase Term
        foreach ($revenue as $r) {
            $r->term = strtoupper($r->term);
        }
        return $revenue;
    }
    
    // Merge Data
    public function merge()
    {
        $laurusCodes = Revenue::where('program_rolled', '!=', '')
            ->distinct('program_rolled')
            ->pluck('program_rolled')
            ->toArray();
            
        $map = ProgramCode::whereIn('laurus', $laurusCodes)
            ->get();

        foreach ($map as $m) {
            if ($m->code) {
                Revenue::where('program_rolled', $m->laurus)
                ->update(['program' => $m->code]);
            }
        }

        $hasSpaces = Revenue::where('program', 'like', '% %')
            ->distinct('program_rolled')
            ->pluck('program_rolled')
            ->toArray();

        return $hasSpaces;
    }
}
