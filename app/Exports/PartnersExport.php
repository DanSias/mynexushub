<?php

namespace App\Exports;

use App\Models\Partner;
use App\Classes\Attributes;

use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;


class PartnersExport implements FromView, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    public $partners;

    public function __construct()
    {
        $this->partners = Partner::orderBy('code', 'asc')
        ->with('accreditation')
        ->with('admissions')
        ->with('earnings')
        ->with('rank')
        ->get();
    }
    
    public function title(): string
    {
        return 'Academic Partners';
    }

    public function view() : View
    {
        return view('excel.partners', [
            'partners' => $this->partners,
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_PERCENTAGE,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
    }
}
