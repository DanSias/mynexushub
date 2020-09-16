<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Models\Program;

class ProgramController extends Controller
{
    public function index($program = '')
    {
        if ($program == '') {
            $programs = Program::orderBy('bu', 'asc')
                ->orderBy('code', 'asc')
                ->with('requirements')
                ->get();
        } else {
            $programs = Program::where('code', $program)
                ->with('requirements')
                ->with('partner')
                ->get();
        }
        return Inertia::render('Programs', ['program' => $program, 'programs' => $programs]);
    }
}
