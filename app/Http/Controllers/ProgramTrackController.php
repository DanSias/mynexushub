<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProgramTrack;

class ProgramTrackController extends Controller
{

    public function index()
    {
        return ProgramTrack::orderBy('program', 'asc')
            ->orderBy('track', 'asc')
            ->get();
    }

    public function store()
    {
        $track = $this->validateRequest();
        $stored = ProgramTrack::create($track);
        return $stored;
    }

    public function destroy(ProgramTrack $programTrack)
    {
        $programTrack->delete();
        return $programTrack;
    }

    public function validateRequest()
    {
        return request()->validate([
            'id' => '',
            'program_id' => '',
            'program' => '',
            'track' => '',
        ]);
    }


    // Tracks for a given program
    public function program($program)
    {
        $tracks = ProgramTrack::where('program', $program)
            ->orderBy('track', 'asc')
            ->with('program')
            ->get();
        return $tracks;
    }

    public function fillProgramId()
    {
        $tracks = ProgramTrack::all();

        foreach ($tracks as $track) {
            $program = Program::where('code', $track->program)
            ->first();

            ProgramTrack::where('id', $track->id)
                ->update(['program_id' => $program->id]);
        }
        return $tracks;
    }
}
