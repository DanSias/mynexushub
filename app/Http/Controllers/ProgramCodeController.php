<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProgramCode;

class ProgramCodeController extends Controller
{

    public function index()
    {
        return ProgramCode::orderBy('program', 'asc')
            ->orderBy('track', 'asc')
            ->get();
    }

    public function store()
    {
        $track = $this->validateRequest();
        $stored = ProgramCode::create($track);
        return $stored;
    }

    public function destroy(ProgramCode $programCode)
    {
        $programCode->delete();
        return $programCode;
    }

    public function validateRequest()
    {
        return request()->validate([
            'id' => '',
            'program_id' => '',
            'code' => '',
            'mosaic' => '',
            'laurus' => ''
        ]);
    }


    // Tracks for a given program
    public function program($program)
    {
        $tracks = ProgramCode::where('code', $program)
            ->orderBy('laurus', 'asc')
            ->with('program')
            ->get();
        return $tracks;
    }

    public function fillProgramId()
    {
        $tracks = ProgramCode::all();

        foreach ($tracks as $track) {
            $program = Program::where('code', $track->program)
            ->first();

            ProgramCode::where('id', $track->id)
                ->update(['program_id' => $program->id]);
        }
        return $tracks;
    }
}
