<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Filter;
use App\Helpers\Metrics\Utilities;

use App\Setting;
use App\Forecast;
use App\BudgetScenario;

// Expected Forecasts
// Programs and channels that have budget or current forecasts
class BudgetsExpected
{
    public $filter;
    public $scenario;
    public $year;

    public function __construct()
    {
        $this->filter = new Filter;
        $sc = Setting::where('type', 'budget')
            ->where('key', 'scenario')
            ->first();
        $yr = Setting::where('type', 'budget')
            ->where('key', 'year')
            ->first();

        $this->scenario = $sc->value;
        $this->year = $yr->value;
    }
    public function months()
    {
        $time = new Time;        
        return $time->allMonths;
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
        $checkYear = (int) $this->year - 1;
        $budgetTable = 'monthly_budget_' . $checkYear;
        $forecastTable = 'monthly_forecast_' . $checkYear;
        $actualsTable = 'monthly_actuals_' . $checkYear;

        $string = 'program, channel, ' . $this->monthsString();

        $actuals = DB::table($actualsTable)
            ->whereIn('program', $this->filter->programsList())
            ->where('metric', '!=', 'CPL')
            ->select(DB::raw($string))
            ->orderBy('total', 'desc')
            ->groupBy('program')
            ->groupBy('channel')
            ->get();

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
        foreach ($actuals as $a) {
            if ($a->total > 0) {
                if (! array_key_exists($a->program, $array)) {
                    $array[$a->program] = [];
                }
                if (! in_array($a->channel, $array[$a->program])) {
                    array_push($array[$a->program], $a->channel);
                }
            }
        }

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

    public function hasBudgets()
    {
        $has = BudgetScenario::where('scenario', $this->scenario)
            ->whereIn('program', $this->filter->programsList())
            ->where('year', $this->year)
            ->get();
        $submitted = [];
        // $approved = [];
        foreach ($has as $h) {
            if (! array_key_exists($h->program, $submitted)) {
                $submitted[$h->program] = [];
            }
            if (! in_array($h->channel, $submitted[$h->program])) {
                array_push($submitted[$h->program], $h->channel);
            }
            // Approved
            // if ($h->approved_at != null) {
            //     if (! array_key_exists($h->program, $approved)) {
            //         $approved[$h->program] = [];
            //     }
            //     if (! in_array($h->channel, $approved[$h->program])) {
            //         array_push($approved[$h->program], $h->channel);
            //     }
            // }
        }
        return $submitted;
        // return ['submitted' => $submitted, 'approved' => $approved];
    }
}
