<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Models\Partner;

class PartnerController extends Controller
{
    public function index($partner = '')
    {
        $partners = Partner::all();

        return Inertia::render('Partners', ['partner' => $partner, 'partners' => $partners]);
    }
}
