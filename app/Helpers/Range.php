<?php

namespace App\Helpers;

use App\Helpers\Time;
use App\Helpers\Filter;

// Date Range (Months)
class Range
{
    public $array;
    public $start;
    public $end;
    public $length;

    public $digitMonth = [
        '01' => 'january',
        '02' => 'february',
        '03' => 'march',
        '04' => 'april',
        '05' => 'may',
        '06' => 'june',
        '07' => 'july',
        '08' => 'august',
        '09' => 'september',
        '10' => 'october',
        '11' => 'november',
        '12' => 'december'
    ];

    public function __construct()
    {
        $range = request()->range;
        if ($range) {
            $this->setRange($range);
        }
    }
    
    public function setRange($range)
    {
        sort($range);
        $this->array = $range;
        $this->start = $range[0];
        $this->end = end($range);
        $this->length = count($range);
    }

    // Array of years in the current range
    public function years()
    {
        $years = [];
        foreach ($this->array as $r) {
            $yr = substr($r, 0, 4);
            if (! in_array($yr, $years)) {
                array_push($years, $yr);
            }
        }
        return $years;
    }

    // Array of years, esch with child array of months
    public function yearsMonths($range = [])
    {
        $range = (count($range) == 0) ? $this->array : $range;
        $yearsMonths = [];
        foreach ($range as $r) {
            $yr = substr($r, 0, 4);
            $mo = substr($r, 4, 2);
            if (!array_key_exists($yr, $yearsMonths)) {
                $yearsMonths[$yr] = [];
            }
            if (!in_array($mo, $yearsMonths[$yr])) {
                array_push($yearsMonths[$yr], $mo);
            }
        }
        return $yearsMonths;
    }

    // Array of SQL Sum string for each year in the range
    // key = year, value = "sum(january) as 01, ...
    public function sumStrings()
    {
        $array = [];
        foreach ($this->yearsMonths() as $year => $months) {
            if (!array_key_exists($year, $array)) {
                $array[$year] = '';
            }
            foreach ($months as $month) {
                $monthText = $this->digitMonth[$month];
                $month = (string) $month;
                $array[$year] .= "SUM($monthText) AS '$month', ";
            }
            $array[$year] = rtrim($array[$year], ', ');
        }
        return $array;
    }

    // For each table type, build arrays with years and month digits
    public function totalStrings()
    {
        $stringArray = [];
        $range['actuals'] = $this->yearsMonths();
        $range['budget'] = $this->yearsMonths();
        $range['forecast'] = $this->yearsMonths($this->forecastYearMonths());

        // If we are getting forecast, remove those months from actuals
        if (count($this->forecastYearMonths()) > 0) {
            foreach ($range['forecast'] as $year => $months) {
                foreach ($months as $key => $value) {
                    if (in_array($value, $range['actuals'][$year])) {
                        $actualsKey = array_search($value, $range['actuals'][$year]);
                        unset($range['actuals'][$year][$actualsKey]);
                    }
                }
            }
        }
        // Build an array for each table type with the SQL sum of months as a string
        foreach ($range as $key => $value) {
            $yearArray = $value;
            $stringArray[$key] = [];
            foreach ($yearArray as $year => $months) {
                if (! array_key_exists($year, $stringArray[$key])) {
                    $stringArray[$key][$year] = '';
                }
                $stringArray[$key][$year] .= 'SUM(';
                foreach ($months as $month) {
                    $monthText = $this->digitMonth[$month];
                    $month = (string) $month;
                    $stringArray[$key][$year] .= $monthText . ' + ';
                }
                $stringArray[$key][$year] = rtrim($stringArray[$key][$year], ' + ');
                $stringArray[$key][$year] .= ') AS total';
            }
        }

        return $stringArray;
    }

    // Date range for forecast months in range
    public function rangeTotalForecastStrings()
    {
        $forecastRange = $this->forecastYearMonths();
        $stringsArray = $this->rangeTotalStrings($forecastRange);

        return $stringsArray;
    }

    public function currentYear()
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        return (int) $year;
    }
    public function currentYearMonth()
    {
        $now = new \DateTime();
        $yearMonth = $now->format('Y') . $now->format('m');
        return $yearMonth;
    }

    // Array of current and future months (for adding forecasts to actuals)
    public function forecastYearMonths()
    {
        $thisMonth = (int) $this->currentYearMonth();
        $array = [];
        $range = $this->array;
        sort($range);
        foreach ($range as $r) {
            if ((int) $r >= $thisMonth && count($range) > 1) {
                array_push($array, $r);
            }
        }
        return $array;
    }

    // Array of tables required, each is an array of each metric to query
    public function tables()
    {
        $filter = new Filter;
        $metric = $filter->metric ?? '';
        $metricArray = ['leads', 'spend'];
        if ($metric == 'leads' || $metric == 'spend') {
            $metricArray = [$metric];
        }

        $array['actuals'] = $array['budget'] = $metricArray;
        
        if (count($this->forecastYearMonths()) > 0) {
            $array['forecast'] = $metricArray;
        }
        return $array;
    }

    public function pace()
    {
        $time = new Time;
        $pace = $time->dates()['actuals']->percentThrough;
        $range = $this->array;
        if (count($range) > 1) {
            $pace = 1;
        } elseif (count($range) == 1 && $range[0] != $this->currentYearMonth()) {
            $pace = 1;
        }
        return $pace;
    }
}