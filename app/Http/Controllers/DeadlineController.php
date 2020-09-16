<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Deadline;

class DeadlineController extends Controller
{
    public function program($program)
    {
        return Deadline::where('program', $program)
            ->whereNotNull('app')
            ->groupBy('year')
            ->groupBy('term')
            ->orderBy('year', 'desc')
            ->orderBy('term', 'asc')
            ->get();
    }
}
