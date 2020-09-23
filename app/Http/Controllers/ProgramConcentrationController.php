<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Program;
use App\Models\ProgramConcentration;

class ProgramConcentrationController extends Controller
{

    public function index()
    {
        return ProgramConcentration::whereNotNull('program')
            ->orderBy('program', 'asc')
            ->orderBy('concentration', 'asc')
            ->get();
    }

    public function store()
    {
        $concentration = $this->validateRequest();
        $stored = ProgramConcentration::create($concentration);
        return $stored;
    }

    public function destroy(ProgramConcentration $programConcentration)
    {
        $programConcentration->delete();
        return 'deleted';
    }

    public function validateRequest()
    {
        return request()->validate([
            'id' => '',
            'program_id' => '',
            'program' => '',
            'concentration' => '',
        ]);
    }

    // Concentrations for a given program
    public function program($program)
    {
        $tracks = ProgramConcentration::where('program', $program)
            ->orderBy('concentration', 'asc')
            ->with('program')
            ->get();
        return $tracks;
    }

    // Database Consistency Check
    public function fillProgramId()
    {
        $tracks = ProgramConcentration::all();

        foreach ($tracks as $track) {
            $program = Program::where('code', $track->program)
            ->first();

            ProgramConcentration::where('id', $track->id)
                ->update(['program_id' => $program->id]);
        }
        return $tracks;
    }
}
