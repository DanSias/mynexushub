<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

use App\Helpers\Filter;

use Carbon\Carbon;
use App\Models\Conversica;
use App\Models\ConversicaFunnel;
use App\Models\ProgramMap;

use App\Models\Deadline;
use App\Models\Revenue;
use App\Models\TermConversion;

class Time
{
    public $allMonths = [
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december'
    ];
    public $monthDigits = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

    public function currentYearMonth()
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        $monthDigits = $this->monthToDigits($now->format('F'));
        return (string)$year . $monthDigits;
    }
    public function fullYearMonths()
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        $array = [];
        foreach ($this->monthDigits as $digits) {
            array_push($array, $year . $digits);
        }
        return $array;
    }
    
    // Current Calendar Year
    public function getYear()
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        return (int) $year;
    }

    // Current Calendar Month
    public function getMonth()
    {
        $now = new \DateTime();
        $month = strtolower($now->format('F'));
        return $month;
    }
    public function getMonthDigits()
    {
        $now = new \DateTime();
        $month = strtolower($now->format('m'));
        return $month;
    }

    // Current ISO Date
    public function getDate()
    {
        $dt = new \DateTime();
        $now = $dt->modify('yesterday');
        $date = $now->format('Y-m-d');
        return $date;
    }

    // Array of all Months in calendar year
    public function allMonths()
    {
        $months = [
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december'
        ];
        return $months;
    }

    public function monthOptions($month = '')
    {
        $string = '';
        $months = $this->allMonths();
        foreach ($months as $mo) {
            if ($mo == $month) {
                $string .= '<option value="' . $mo . '" selected>' . ucfirst($mo) . '</option>';
            } else {
                $string .= '<option value="' . $mo . '">' . ucfirst($mo) . '</option>';
            }
        }
        return $string;
    }

    // Key Time Formats for Data Actuals
    public function actuals($obj = '')
    {
        $actualsUpdate = DB::table('data_updates')
            ->where('table_name', 'like', 'monthly_actuals_%')
            ->orderBy('id', 'desc')
            ->first();
        $actualsDate = $actualsUpdate->last_updated;

        // Date that the database was updated (object)
        // Data is complete through previous day
        $dateTime = new \DateTime($actualsDate);
        $dateTime = $dateTime->modify('-1 day');
        $actualsDate = $dateTime->format('Y-m-d');
        $now = new \DateTime();

        // Updated Date Components
        $dataYear = (int) $dateTime->format('Y');
        $dataMonth = $dateTime->format('n');
        $dataDay = $dateTime->format('j');

        // Date Values of Request
        if ($obj != '') {
            $requestYear = (int)$obj->year ? : '';
            $requestMonth = $obj->month ? : '';
        } else {
            $requestYear = '';
            $requestMonth = '';
        }
        

        if ($requestYear != $dataYear) {
            $monthPercent = 1;
        } else {
            $monthDays = cal_days_in_month(CAL_GREGORIAN, $dataMonth, $dataYear);
            $monthPercent = $dataDay / $monthDays;
        }

        // Complete Percentages of Current Year
        $complete = [];
        $allMonths = $this->allMonths();
        foreach ($allMonths as $mo) {
            $complete[$mo] = 0;
        }
        $completeMonths = $this->completeMonths(strtolower($dateTime->format('F')));
        foreach ($completeMonths as $mo) {
            $complete[$mo] = 1;
        }
        $complete[strtolower($dateTime->format('F'))] = $monthPercent;
        if ($requestYear < $dataYear) {
            foreach ($allMonths as $mo) {
                $complete[$mo] = 1;
            }
        }

        $returnObject = new \StdClass();

        $returnObject->date = $actualsDate;
        $returnObject->dateTime = $dateTime;
        $returnObject->percent = $monthPercent;
        $returnObject->requestYear = $requestYear;
        $returnObject->dataMonth = $allMonths[$dataMonth - 1];
        $returnObject->dataYear = $dataYear;
        $returnObject->complete = $complete;
        $returnObject->completeMonths = $completeMonths;

        return $returnObject;
    }

    public function actualsYearMonth()
    {
        $actualsUpdate = DB::table('data_updates')
            ->where('table_name', 'like', 'monthly_actuals_%')
            ->orderBy('id', 'desc')
            ->first();
        $actualsDate = $actualsUpdate->last_updated;

        $dateTime = new \DateTime($actualsDate);
        $dateTime->modify('-1 day');
        $dataYearMonth = $dateTime->format('Ym');

        $returnObject = new \StdClass();
        $returnObject->year = $dateTime->format('Y');
        $returnObject->date = $dateTime->format('Y-m-d');
        $returnObject->current = $dataYearMonth;
        $yearMonths = $this->yearMonths($returnObject->year);
        $returnObject->monthPercent = $this->monthPercent($returnObject->date);
        foreach ($yearMonths as $mo) {
            if (intval($mo) < intval($returnObject->current)) {
                $returnObject->complete[$mo] = 1;
            } elseif ($mo == $returnObject->current) {
                $returnObject->complete[$mo] = $returnObject->monthPercent;
            } else {
                $returnObject->complete[$mo] = 0;
            }
        }
        return $returnObject;
    }

    public function actualsCompleteYearMonths()
    {
        $current = $this->actualsYearMonth()->current;
        $year = $this->getYear();
        $all = $this->singleYearMonths($year);
        $completeArray = [];
        foreach ($all as $mo) {
            if ($mo == $current) {
                break;
            }
            array_push($completeArray, $mo);
        }
        return $completeArray;
    }

    public function monthPercent($date = '')
    {
        if ($date == '') {
            $date = $this->getDate();
        }

        // Date that the database was updated (object)
        $dateTime = new \DateTime($date);

        $year = $dateTime->format('Y');
        $month = $dateTime->format('n');
        $day = $dateTime->format('j');

        $monthDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $monthPercent = $day / $monthDays;

        return $monthPercent;
    }

    // Month percent for a given table based on data_updates table
    public function tablePercent($table, $month)
    {
        $yr = substr($table, -4);
        if ($yr < 2017) {
            return 1;
        }
        $lastUpdated = DB::table('data_updates')
            ->where('table_name', $table)
            ->orderBy('last_updated', 'desc')
            ->first();

        $date = $lastUpdated->last_updated;

        // Date that the database was updated (object)
        $now = new \DateTime();
        $dateTime = new \DateTime($date);
        $dateTime->modify('-1 days');

        $dataYear = $dateTime->format('Y');
        $dataMonth = $dateTime->format('n');
        $dataDay = $dateTime->format('j');

        $monthDays = cal_days_in_month(CAL_GREGORIAN, $dataMonth, $dataYear);

        $nowMonth = strtolower($now->format('F'));
        if ($nowMonth == $month) {
            $monthPercent = $dataDay / $monthDays;
        } else {
            $monthPercent = 1;
        }
        return $monthPercent;
    }

    // Prepare SQL string: SUM(month) as month for all months
    public function monthSumString($month = '', $year = '')
    {
        $now = new \DateTime();
        $nowYear = $now->format('Y');

        $allMonths = $this->allMonths();
        $monthSumString = '';
        foreach ($allMonths as $mo) {
            if ($mo == $month) {
                if ($year == '' || $year == $nowYear) {
                    break;
                }
            } else {
                $monthSumString .= 'SUM(' . $mo . ') as ' . $mo . ', ';
            }
        }
        $monthSumString = rtrim($monthSumString, ', ');
        return $monthSumString;
    }

    public function monthSumAll()
    {
        $months = $this->allMonths();
        $string = '';
        foreach ($months as $mo) {
            $string .= 'SUM(' . $mo . ') as ' . $mo . ', ';
        }
        $string = rtrim($string, ', ');
        return $string;
    }

    public function monthSumDigits()
    {
        $months = $this->allMonths();
        $string = '';
        $i = 1;
        foreach ($months as $mo) {
            if ($i < 10) {
                $digits = '0' . (string) $i;
            } else {
                $digits = (string) $i;
            }
            $string .= 'SUM(' . $mo . ') as \'' . $digits . '\', ';
            $i++;
        }
        $string = rtrim($string, ', ');
        return $string;
    }

    // Complete Months given a passed month
    public function completeMonths($month = '', $year = '')
    {
        if ($month == '') {
            $month = $this->getMonth();
        }
        
        if ($year != '') {
            $now = new \DateTime();
        }
        
        $months = $this->allMonths();

        $array = [];
        foreach ($months as $mo) {
            if ($mo == strtolower($month)) {
                break;
            }
            array_push($array, $mo);
        }
        return $array;
    }

    // Remaining months (includes partial current month) - for calculating foreacasted totals
    public function remainingMonths($month = '')
    {
        if ($month == '') {
            $month = $this->getMonth();
        }
        $allMonths = $this->allMonths();
        $remainingMonths = [];
        $inc = 0;
        foreach ($allMonths as $mo) {
            if ($mo == $month) {
                $inc++;
            }
            if ($inc > 0) {
                array_push($remainingMonths, $mo);
            }
        }
        return $remainingMonths;
    }

    // Pending months that need to be forecasted
    public function forecastMonths($month)
    {
        $months = $this->allMonths();
        $completeMonths = $this->completeMonths($month);
        
        $array = [];
        foreach ($months as $mo) {
            if (in_array($mo, $completeMonths)) {
                continue;
            } else {
                array_push($array, $mo);
            }
        }
        return $array;
    }

    // Given an array of year-months, what range should be forecasted (current and future months)
    public function forecastYearMonths($range)
    {
        $thisMonth = (Int) $this->currentYearMonth();
        $array = [];
        foreach ($range as $r) {
            // If range has more than one month and is current or future
            if ((Int) $r >= $thisMonth && count($range) > 1) {
                array_push($array, $r);
            }
        }
        return $array;
    }

    // For metrics with a given timeframe, which months are currently complete
    // Ex: Quality 30 we would look back 30 days to see which months had elapsed on that date
    public function buildMonthArray($date, $offset = 0)
    {
        $dateTime = new \DateTime($date);
        if ($offset > 0) {
            $dateTime->add(\DateInterval::createFromDateString('-' . $offset . ' days'));
        }
        $month = $dateTime->format('F');
        $month = strtolower($month);
        $months = $this->allMonths();
        $monthArray = [];
        foreach ($months as $mo) {
            if ($mo == $month) {
                break;
            } else {
                array_push($monthArray, $mo);
            }
        }
        return $monthArray;
    }

    // How many days ago was a given date?
    public function daysAgo($date)
    {
        $startDate = new \DateTime($date);
        $stopDate = new \DateTime();
                
        $age = $startDate->diff($stopDate);
        $interval = $age->format('%a');
        if ($interval == 0) {
            return 'New';
        } elseif ($interval == 1) {
            return '1 Day';
        } elseif ($interval > 1) {
            return $interval . ' Days';
        } else {
            return '';
        }
    }

    // Convert month to 2 digit string
    public function monthToDigits($month = '')
    {
        $month = strtolower($month);

        if ($month == '') {
            return null;
        }

        $array = [
            'january' => '01',
            'february' => '02',
            'march' => '03',
            'april' => '04',
            'may' => '05',
            'june' => '06',
            'july' => '07',
            'august' => '08',
            'september' => '09',
            'october' => '10',
            'november' => '11',
            'december' => '12',
        ];
        
        return $array[$month];
    }
    // Single digit
    public function monthToInteger($month = '')
    {
        $month = strtolower($month);

        if ($month == '') {
            return null;
        }

        $array = [
            'january' => '1',
            'february' => '2',
            'march' => '3',
            'april' => '4',
            'may' => '5',
            'june' => '6',
            'july' => '7',
            'august' => '8',
            'september' => '9',
            'october' => '10',
            'november' => '11',
            'december' => '12',
        ];

        return (int)$array[$month];
    }

    // Array of elements for past 2 complete years and current year
    public function yearMonths($year = '')
    {
        if ($year == '') {
            $year = $this->getYear();
        }
        $yearArray = [$year - 2, $year - 1, $year];
        $monthArray = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $returnArray = [];
        foreach ($yearArray as $year) {
            foreach ($monthArray as $month) {
                array_push($returnArray, $year . $month);
            }
        }
        return $returnArray;
    }
    public function singleYearMonths($year = '')
    {
        if ($year == '') {
            $year = $this->getYear();
        }
        $monthArray = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $returnArray = [];
        foreach ($monthArray as $month) {
            array_push($returnArray, $year . $month);
        }
        return $returnArray;
    }

    public function nameToDigits($name)
    {
        $monthArray = [
            'january' => '01',
            'february' => '02',
            'march' => '03',
            'april' => '04',
            'may' => '05',
            'june' => '06',
            'july' => '07',
            'august' => '08',
            'september' => '09',
            'october' => '10',
            'november' => '11',
            'december' => '12'
        ];

        return $monthArray[strtolower($name)];
    }
    public function digitsToMonth($digits)
    {
        $monthArray = [
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

        return $monthArray[$digits];
    }

    // Excel formatted date (mm/cc/yy -or- m/d/yy)
    public function excelToName($date)
    {
        $dateArray = explode('/', $date);
        $month = $dateArray[0];
        $index = $month - 1;
        $months = $this->allMonths;
        return $months[$index];
    }
    public function excelToYear($date)
    {
        $dateArray = explode('/', $date);
        $year = $dateArray[2];
        if ($year < 1000) {
            $year = '20' . $year;
        }
        return (int) $year;
    }
    public function excelToIso($date)
    {
        $dateArray = explode('/', $date);
        $month = $dateArray[0];
        if ($month < 10) {
            $month = '0' . $month;
        }
        $day = $dateArray[1];        
        if ($day < 10) {
            $day = '0' . $day;
        }
        $year = $dateArray[2];
        if ($year < 1000) {
            $year = '20' . $year;
        }
        return $year . '-' . $month . '-' . $day;
    }

    // Conversica Dates
    public function latestConversicaDate()
    {
        $time = new Time;
        $year = $time->getYear();

        $latestMonth = Conversica::where('year', $year)
            ->max('month');

        $latestDay = Conversica::where('year', $year)
            ->where('month', $latestMonth)
            ->max('day');
            
        // Prepend zeros if needed
        if ($latestMonth < 10) {
            $latestMonth = '0' . $latestMonth;
        }
        // Updated date returned is one after latest in DB
        $latestDay++;
        if ($latestDay < 10) {
            $latestDay = '0' . $latestDay;
        }
            
        return (int)$year . '-' . $latestMonth . '-' . $latestDay;
    }
    public function latestConversicaFunnelDate()
    {
        $time = new Time;
        $year = $time->getYear();

        $latestMonth = ConversicaFunnel::where('year', $year)
            ->max('month');

        $latestDay = ConversicaFunnel::where('year', $year)
            ->where('month', $latestMonth)
            ->max('day');

        // Prepend zeros if needed
        if ($latestMonth < 10) {
            $latestMonth = '0' . $latestMonth;
        }
        // Updated date returned is one after latest in DB
        $latestDay++;
        if ($latestDay < 10) {
            $latestDay = '0' . $latestDay;
        }

        return (int) $year . '-' . $latestMonth . '-' . $latestDay;
    }

    // Data Updated Dates
    public function dates()
    {
        $dates = DB::table('data_updates')
            ->orderBy('last_updated', 'desc')
            // ->groupBy('table_name')
            ->get(['table_name', 'last_updated']);
        $dateArray = [];
        foreach ($dates as $d) {
            if (!array_key_exists($d->table_name, $dateArray)) {
                $dateArray[$d->table_name] = $d->last_updated;
            }
        }

        // Conversica date from data
        // $conversicaDate = $this->latestConversicaDate();
        // $dateArray['conversica'] = $conversicaDate;
        // $conversicaFunnelDate = $this->latestConversicaFunnelDate();
        // $dateArray['conversica_funnel'] = $conversicaFunnelDate;

        $returnArray = [];
        // Loop through and build object
        foreach ($dateArray as $key => $value) {
            $dateTime = new \DateTime($value);
            $through = $dateTime->modify('-1 day');

            $returnArray[$key] = new \StdClass();
            $returnArray[$key]->name = $key;
            $returnArray[$key]->updated = $value;
            $returnArray[$key]->through = $through->format('Y-m-d');
            $returnArray[$key]->percentThrough = $this->monthPercent($through->format('Y-m-d'));
            $returnArray[$key]->yearMonth = $through->format('Ym');
            $returnArray[$key]->yearPercent = $this->yearPercent($through->format('Y-m-d'));
        }

        $now = new \DateTime();
        $year = $now->format('Y');
        $ly = $year - 1;

        $actualsTable = 'monthly_actuals_' . $year;
        $budgetTable = 'monthly_budget_' . $year;
        $forecastTable = 'monthly_forecast_' . $year;

        $actualsTableLast = 'monthly_actuals_' . $ly;
        // $budgetTableLast = 'monthly_budget_' . $ly;
        $forecastTableLast = 'monthly_forecast_' . $ly;

        // Set Shorthand Keys
        $returnArray['actuals'] = (isset($returnArray[$actualsTable])) ? $returnArray[$actualsTable] : $returnArray[$actualsTableLast];
        $returnArray['budget'] = (isset($returnArray[$budgetTable])) ? $returnArray[$budgetTable] : null;
        $returnArray['forecast'] = (isset($returnArray[$forecastTable])) ? $returnArray[$forecastTable] : $returnArray[$forecastTableLast];
        $returnArray['metrics'] = (isset($returnArray['monthly_metrics'])) ? $returnArray['monthly_metrics'] : null;

        $returnArray['leading'] = (isset($returnArray['leading_indicators'])) ? $returnArray['leading_indicators'] : null;
        $returnArray['lagging'] = (isset($returnArray['lagging_indicators'])) ? $returnArray['lagging_indicators'] : null;

        $start = (is_object($returnArray['actuals'])) ?  new \DateTime($returnArray['actuals']->through) : new \DateTime($returnArray['metrics']->through);
        
        $fifteen = clone $start;
        $fifteen->modify('-15 days');
        $fifteen->modify('last day of previous month');

        $thirty = clone $start;
        $thirty->modify('-30 days');
        $thirty->modify('last day of previous month');

        $sixty = clone $start;
        $sixty->modify('-60 days');
        $sixty->modify('last day of previous month');

        $ninety = clone $start;
        $ninety->modify('-90 days');
        $ninety->modify('last day of previous month');

        $onetwenty = clone $start;
        $onetwenty->modify('-120 days');
        $onetwenty->modify('last day of previous month');

        $oneeighty = clone $start;
        $oneeighty->modify('-180 days');
        $oneeighty->modify('last day of previous month');


        $mature[0] = $start->format('Ym');
        $mature[15] = $fifteen->format('Ym');
        $mature[30] = $thirty->format('Ym');
        $mature[60] = $sixty->format('Ym');
        $mature[90] = $ninety->format('Ym');
        $mature[120] = $onetwenty->format('Ym');
        $mature[180] = $oneeighty->format('Ym');

        $returnArray['mature'] = $mature;

        $returnArray['complete'] = $this->actualsYearMonth();

        return $returnArray;
    }

    // Array of years contained in a yearmonth range array
    public function rangeYears($range)
    {
        $array = [];
        foreach ($range as $r) {
            $yr = substr($r, 0, 4);
            if (!in_array($yr, $array)) {
                array_push($array, $yr);
            }
        }
        return $array;
    }

    // Array of years, each with an array of text months
    public function rangeYearsMonths($range)
    {
        $array = [];
        foreach ($range as $r) {
            $yr = substr($r, 0, 4);
            $mo = substr($r, 4, 2);
            if (!array_key_exists($yr, $array)) {
                $array[$yr] = [];
            }
            if (! in_array($mo, $array[$yr])) {
                array_push($array[$yr], $mo);
            }
        }
        return $array;
    }

    // Sum string for each year in the range
    public function rangeSumStrings($range)
    {
        $array = [];
        $yearsMonths = $this->rangeYearsMonths($range);
        foreach ($yearsMonths as $year => $months) {
            if (!array_key_exists($year, $array)) {
                $array[$year] = '';
            }
            foreach ($months as $month) {
                $monthText = $this->digitsToMonth($month);
                $month = (string)$month;
                $array[$year] .= "SUM($monthText) AS '$month', ";
            }
            $array[$year] = rtrim($array[$year], ', ');
        }
        return $array;
    }

    // For each table type, build arrays with years and month digits
    public function rangeTotalStrings($range)
    {
        $array = [];
        $types = ['actuals', 'budget', 'forecast'];
        $yearsMonths = $this->rangeYearsMonths($range);
        $forecastRange = $this->forecastYearMonths($range);

        $tableRange['actuals'] = $yearsMonths;
        $tableRange['budget'] = $yearsMonths;
        $tableRange['forecast'] = $this->rangeYearsMonths($forecastRange);

        // If we are getting forecast, remove those months from actuals
        if (count($forecastRange) > 0) {
            foreach ($tableRange['forecast'] as $year => $months) {
                foreach ($months as $key => $value) {
                    if (in_array($value, $tableRange['actuals'][$year])) {
                        $actualsKey = array_search($value, $tableRange['actuals'][$year]);
                        unset($tableRange['actuals'][$year][$actualsKey]);
                    }
                }
            }
        }
        // Build an array for each table type with the SQL sum of months as a string
        foreach ($types as $type) {
            $yearArray = $tableRange[$type];
            $array[$type] = [];
            foreach ($yearArray as $year => $months) {
                if (!array_key_exists($year, $array[$type])) {
                    $array[$type][$year] = '';
                }
                $array[$type][$year] .= 'SUM(';
                foreach ($months as $month) {
                    $monthText = $this->digitsToMonth($month);
                    $month = (string)$month;
                    $array[$type][$year] .= $monthText . ' + ';
                }
                $array[$type][$year] = rtrim($array[$type][$year], ' + ');
                $array[$type][$year] .= ') AS total';
            }
        }

        return $array;
    }

    // Date range for forecast months in range
    public function rangeTotalForecastStrings($range)
    {
        $forecastRange = $this->forecastYearMonths($range);
        $stringsArray = $this->rangeTotalStrings($forecastRange);

        return $stringsArray;
    }


    // ISO date format of the last day of the previous month
    public function isoEndLastMonth()
    {
        $now = new \DateTime();
        $lastDate = $now->modify('last day of previous month');
        $endDate = $lastDate->format('Y-m-d');

        return $endDate;
    }
    // ISO date format of yesterday
    public function isoYesterday()
    {
        $now = new \DateTime();
        $lastDate = $now->modify('yesterday');
        $endDate = $lastDate->format('Y-m-d');

        return $endDate;
    }
    // ISO date format of x days ago
    public function isoDaysAgo($x)
    {
        $now = new \DateTime();
        $lastDate = $now->modify("$x days ago");
        $endDate = $lastDate->format('Y-m-d');

        return $endDate;
    }

    // Application Deadlines by program and
    public function deadlines()
    {
        $filter = new Filter;
        $deadlines = Deadline::where('year', '>=', 2018)
            ->whereIn('program', $filter->programsList())
            ->orderBy('program', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc')
            ->get(['program', 'year', 'term', 'app', 'start']);
        // return $deadlines;
        $returnObject = $this->formatDeadlines($deadlines);
        
        return $returnObject;
    }


    // Format the deadlines for readability
    public function formatDeadlines($deadlines)
    {
        $maps = ProgramMap::get();
        $mapArray = [];
        foreach ($maps as $map) {
            $mapArray[$map->laurus] = $map->code;
        }

        foreach ($deadlines as $d) {
            $d->term = strtoupper($d->term);
            if (strpos($d->term, 'A') !== false) {
                $d->semester = 'Spring';
            } elseif (strpos($d->term, 'B') !== false) {
                $d->semester = 'Summer';
            } elseif (strpos($d->term, 'C') !== false) {
                $d->semester = 'Fall';
            } else {
                $d->semester = '';
            }
            // Date Details
            $carbon = ($d->app) ? new Carbon($d->app) : '';
            $d->when = ($d->app) ? $carbon->diffForHumans() : '';
            if ($carbon !== '' && $carbon->isPast()) {
                $d->past = true;
            } else {
                $d->past = false;
            }
            $d->text = ($d->app) ? $carbon->format('F j, Y') : '';
            $d->days = ($d->app) ? $carbon->diffInDays() : '';

            $d->program = (isset($mapArray[$d->program])) ? $mapArray[$d->program] : $d->program;
            $d->code = $d->program;
            $d->programYearTerm = $d->program . $d->year . $d->term;
            if ($d->app) {
                $explode = explode('-', $d->app);
                $yr = (int)$explode[0];
                $mo = (int)$explode[1];
                $day = (int)$explode[2];

                // Last Complete Month
                $lm = $mo - 1;
                // On or after the 15th, include deadline month
                if ($day >= 15) {
                    $lm = $mo;
                }

                if ($lm == 0) {
                    $yr = $yr--;
                    $lm = 12;
                }
                if ($lm < 10) {
                    $lm = 0 . $lm;
                }
                $d->close = $yr . $lm;
                $d->open = (int)$d->close - 99;
            }
        }
        
        $deadlines = $deadlines->unique('programYearTerm');

        // Deadlines with Data
        $dataTerms = $this->dataTerms();
        foreach ($deadlines as $key => $value) {
            if (! in_array($value->programYearTerm, $dataTerms)) {
                unset($deadlines[$key]);
            }
        }
        
        $nowYear = $this->getYear();

        $next = $deadlines->where('past', false)
            ->sortBy('days')
            ->where('year', '>=', $nowYear)
            ->first();
        if (is_null($next)) {
            $next = $deadlines->where('past', true)
                ->sortBy('days')
                // ->where('year', '=', $nowYear)
                ->first();
        }

        $returnObject = new \StdClass();
        $returnObject->next = $next;
        $returnObject->deadlines = $deadlines;

        return $returnObject;
    }

    // Terms from revenue and term conversion reports
    public function revenueTerms()
    {
        $revTerms = Revenue::where('year', '>=', 2018)
            ->whereNotNull('program')
            ->select(DB::raw('program, year, term, starts + plan_starts as starts'))
            ->where('starts', '>', 0)
            ->orWhere('plan_starts', '>', 0)
            ->groupBy('program')
            ->groupBy('year')
            ->groupBy('term')
            ->orderBy('program', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc')
            ->get();
        return $revTerms;
    }

    public function conversionTerms()
    {
        $conversionTerms = TermConversion::where('year', '>=', 2018)
            ->whereNotNull('program')
            ->select(DB::raw('program, year, term, starts + plan_starts as starts'))
            ->where('starts', '>', 0)
            ->orWhere('plan_starts', '>', 0)
            ->groupBy('program')
            ->groupBy('year')
            ->groupBy('term')
            ->orderBy('program', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc')
            ->get();
        return $conversionTerms;
    }

    // Terms in the revenue / term conversion tables - Format of Program . Year . Term
    public function dataTerms()
    {
        $revTerms = $this->revenueTerms();
        $convTerms = $this->conversionTerms();

        $array = [];

        foreach ($revTerms as $term) {
            $pyt = $term->program . $term->year . strtoupper($term->term);
            array_push($array, $pyt);
        }
        foreach ($convTerms as $term) {
            $pyt = $term->program . $term->year . strtoupper($term->term);
            if (! in_array($pyt, $array)) {
                array_push($array, $term->program . $term->year . strtoupper($term->term));
            }
        }

        sort($array);

        return $array;
    }

    public function yearPercent($date)
    {
        $c = new Carbon;
        
        $start = $c->parse('first day of january');
        $now = $c->parse($date);

        $diff = $start->diffInDays($now);

        $days = 365 + date('L');
        $pct = $diff / $days;

        return $pct;
    }

    public function actualsThrough()
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        $table = 'monthly_actuals_' . $year;
        $updated = DB::table('data_updates')
            ->where('table_name', $table)
            ->orderBy('last_updated', 'desc')
            ->first()
            ->last_updated;
        $c = new Carbon($updated);
        // Data is through day before updated
        $c->addDays('-1');
        return $c;
    }
}
