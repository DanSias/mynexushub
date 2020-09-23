<?php

namespace App\Exports;

use App\Models\Program;
use App\Models\ProgramRequirement;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProgramsExport implements FromView, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Degree Programs';
    }

    public function view() : View
    {
        $programs = Program::orderBy('bu', 'asc')
            ->orderBy('code', 'asc')
            ->with('requirements')
            ->get();
            
        return view('excel.programs', [
            'programs' => $programs,
        ]);
    }
}
