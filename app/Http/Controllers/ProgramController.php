<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Helpers\Filter;

use App\Models\Program;
use App\Models\Partner;

class ProgramController extends Controller
{
    public function index($program = '', $action = '')
    {
        $filter = new Filter;
        if ($program == '') {
            $programs = Program::orderBy('bu', 'asc')
                ->orderBy('code', 'asc')
                ->with('requirements')
                ->get();
        } elseif ($program == 'new') {
            $programs[0] = new Program;
        } else {
            $programs = Program::where('code', $program)
                ->with('requirements')
                ->with('partner')
                ->with('home')
                ->get();
        }

        return Inertia::render('Programs', 
            [
                'filter' => $filter,
                'program' => $program, 
                'programs' => $programs, 
                'action' => $action
            ]);
    }

    public function store()
    {
        $program = $this->validateRequest();
        $stored = Program::create($program);
        return $stored;
    }

    public function update(Program $program)
    {
        $program->update($this->validateRequest());
        return $program;   
    }

    public function validateRequest()
    {
        return request()->validate([
            'id' => '',
            'code' => 'required',
            'active' => '',
            'name' => '',
            'full_name' => '',
            'partner' => '',
            'strategy' => '',
            'ltv' => '',
            'bu' => '',
            'location' => '',
            'vertical' => '',
            'subvertical' => '',
            'level' => '',
            'type' => '',
            'priority' => '',
            'accreditation' => '',
            'online_percent' => '',
            'start_year' => '',
            'start_month' => '',
            'entry_points' => '',
            'renewal_date' => '',
            'notes' => '',
            'contacts' => '',
        ]);
    }

    // Lists for filter select options
    public function buList()
    {
        $active = request()->active ?? ['TRUE', 'PENDING'];

        return Program::whereIn('active', $active)
            ->whereNotNull('bu')
            ->where('bu', '>', 0)
            ->orderBy('bu', 'asc')
            ->distinct()
            ->pluck('bu')
            ->toArray();
    }
    public function typeList()
    {
        $active = request()->active ?? ['TRUE', 'PENDING'];

        return Program::whereIn('active', $active)
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->orderBy('type', 'asc')
            ->distinct()
            ->pluck('type')
            ->toArray();
    }

    public function partnerList()
    {
        $active = request()->active ?? ['TRUE', 'PENDING'];

        $id = Program::whereIn('active', $active)
            ->whereNotNull('partner_id')
            ->distinct()
            ->pluck('partner_id')
            ->toArray();
        
        return Partner::whereIn('id', $id)
            ->orderBy('code', 'asc')
            ->get(['id', 'code']);
    }

    public function verticalList()
    {
        $active = request()->active ?? ['TRUE', 'PENDING'];

        return Program::whereIn('active', $active)
            ->whereNotNull('vertical')
            ->where('vertical', '!=', '')
            ->orderBy('vertical', 'asc')
            ->distinct()
            ->pluck('vertical')
            ->toArray();
    }

    public function subverticalList($vertical = '')
    {
        $active = request()->active ?? ['TRUE', 'PENDING'];
        if ($vertical == '') {
            return Program::whereIn('active', $active)
                ->whereNotNull('subvertical')
                ->where('subvertical', '!=', '')
                ->orderBy('subvertical', 'asc')
                ->distinct()
                ->pluck('subvertical')
                ->toArray();
        } else {
            return Program::whereIn('active', $active)
                ->where('vertical', $vertical)
                ->whereNotNull('subvertical')
                ->where('subvertical', '!=', '')
                ->orderBy('subvertical', 'asc')
                ->distinct()
                ->pluck('subvertical')
                ->toArray();
        }
    }
}
