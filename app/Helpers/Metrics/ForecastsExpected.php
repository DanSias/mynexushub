<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Filter;
use App\Helpers\Metrics\Utilities;

use App\Models\Setting;
use App\Models\Forecast;

// Expected Forecasts
// Programs and channels that have budget or current forecasts
class ForecastsExpected
{
    public $filter;
    public $month;
    public $year;

    public function __construct()
    {
        $this->filter = new Filter;
        $mo = Setting::where('type', 'forecast')
            ->where('key', 'month')
            ->first();
        $yr = Setting::where('type', 'forecast')
            ->where('key', 'year')
            ->first();

        $this->month = $mo->value;
        $this->year = (int) $yr->value;

    }
    public function months()
    {
        $time = new Time;
        $months = $time->allMonths;
        $month = $this->month;
        $add = false;
        $array = [];
        foreach ($months as $mo) {
            if ($mo == $month) {
                $add = true;
            }
            if ($add === true) {
                array_push($array, $mo);
            }
        }
        return $array;
    }
    public function monthsString()
    {
        $string = 'SUM(';
        foreach ($this->months() as $month ) {
            $string .= $month . ' + ';
        }
        $string = rtrim($string, ' + ');
        $string .= ') as total';
        return $string;
    }
    
    public function programChannels()
    {
        $budgetTable = 'monthly_budget_' . $this->year;
        $forecastTable = 'monthly_forecast_' . $this->year;
        $string = 'program, channel, ' . $this->monthsString();

        $forecast = DB::table($forecastTable)
            ->whereIn('program', $this->filter->programsList())
            ->where('metric', '!=', 'CPL')
            ->select(DB::raw($string))
            ->orderBy('total', 'desc')
            ->groupBy('program')
            ->groupBy('channel')
            ->get();
        
        $budget = DB::table($budgetTable)
            ->whereIn('program', $this->filter->programsList())
            ->where('metric', '!=', 'CPL')
            ->select(DB::raw($string))
            ->orderBy('total', 'desc')
            ->groupBy('program')
            ->groupBy('channel')
            ->get();

        $array = [];
        foreach ($forecast as $f) {
            if ($f->total > 0) {
                if (! array_key_exists($f->program, $array)) {
                    $array[$f->program] = [];
                }
                if (! in_array($f->channel, $array[$f->program])) {
                    array_push($array[$f->program], $f->channel);
                }
            }
        }
        foreach ($budget as $f) {
            if ($f->total > 0) {
                if (! array_key_exists($f->program, $array)) {
                    $array[$f->program] = [];
                }
                if (! in_array($f->channel, $array[$f->program])) {
                    array_push($array[$f->program], $f->channel);
                }
            }
        }

        return $array;
    }

    public function hasForecasts()
    {
        $has = Forecast::where('month', $this->month)
            // ->whereIn('program', $this->filter->programsList())
            ->where('year', $this->year)
            ->with('program')
            ->get();
        $submitted = [];
        $approved = [];
        foreach ($has as $h) {
            if (! array_key_exists($h->program->code, $submitted)) {
                $submitted[$h->program->code] = [];
            }
            if (! in_array($h->channel, $submitted[$h->program->code])) {
                array_push($submitted[$h->program->code], $h->channel);
            }
            // Approved
            if ($h->approved_at != null) {
                if (! array_key_exists($h->program->code, $approved)) {
                    $approved[$h->program->code] = [];
                }
                if (! in_array($h->channel, $approved[$h->program->code])) {
                    array_push($approved[$h->program->code], $h->channel);
                }
            }
        }

        return ['submitted' => $submitted, 'approved' => $approved];
    }
}
