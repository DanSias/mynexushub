<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Models\Partner;

class PartnerController extends Controller
{
    public function index($partner = '')
    {
        $partners = $this->data();

        return Inertia::render('Partners', ['partner' => $partner, 'partners' => $partners]);
    }

    public function data()
    {
        return Partner::orderBy('code', 'asc')
            ->with('accreditation')
            ->with('admissions')
            ->with('earnings')
            ->with('rank')
            ->withCount('programs')
            ->withCount('activePrograms')
            ->get();
    }
}
