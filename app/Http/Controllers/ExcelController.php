<?php

namespace App\Http\Controllers;

use Excel;
use Illuminate\Http\Request;

use App\Exports\AttributesExport;

class ExcelController extends Controller
{

    public function attributes(Request $request)
    {
        $export = new AttributesExport;
        return Excel::download($export, 'Program & Partner Attributes.xlsx');
    }
}
