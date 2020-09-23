<?php

namespace App\Exports;

use App\Exports\ProgramsExport;
use App\Exports\PartnersExport;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttributesExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new PartnersExport;
        $sheets[] = new ProgramsExport;

        return $sheets;
    }
}
