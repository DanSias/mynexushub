<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Program;
use App\Models\Forecast;
use App\Models\Setting;
use App\Models\UserRole;

use App\Helpers\Time;
use App\Helpers\Filter;
use App\Helpers\Metrics\ForecastsExpected;

class ForecastController extends Controller
{
    // Forecast Settings
    public $year;
    public $month;
    public $status;
    
    public function __construct()
    {
        $settings = Setting::where('type', 'forecast')->get();
        foreach ($settings as $setting) {
            $key = $setting->key;
            $value = $setting->value;
            $this->$key = $value;
        }
    }

    public function index($program = '', $channel = '')
    {
        $currentUser = auth()->user();
        $role = UserRole::where('user_id', $currentUser->id)->first();

        $filter = new Filter;
        $filter->group = 'code';
        $filter->active = ['TRUE'];
        if ($role->location) {
            $filter->location = json_decode($role->location);
        }
        if ($role->bu) {
            $filter->bu = json_decode($role->bu);
        }
        if ($role->channel) {
            $filter->channel = json_decode($role->channel);
        }
        $filter->keys = $filter->keys();
        $filter->programs = $filter->programsList();

        return Inertia::render('Forecast', 
            [
                'filter' => $filter,
                'year' => (int) $this->year,
                'month' => $this->month, 
                'status' => $this->status, 
                'program' => $program,
                'channel' => $channel
            ]);
    }


    public function save($program, $channel, Request $request)
    {
        if ($this->status == 'closed') {
            return 'closed';
        }

        $programId = Program::where('code', $program)->pluck('id')->first();
        $sum = [
            'leads' => 0,
            'spend' => 0
        ];

        $currentUser = Auth::user();
        $userId = $currentUser->id;

        $initArray = [];
        $saveArray = [];

        $time = new Time;
        $allMonths = $time->allMonths;
        foreach ($request->all() as $init => $metrics) {
            // Value is the metric array with keys leads, spend, cpl
            foreach ($metrics as $metric => $months) {
                if ($metric == 'leads' || $metric == 'spend') {
                    
                    $months['user_id'] = $userId;
                    $months['program_id'] = $programId;
                    $months['channel'] = $channel;
                    $months['metric'] = $metric;
                    $months['initiative'] = $init;
                    $months['year'] = $this->year;
                    $months['month'] = $this->month;

                    array_push($saveArray, $months);
                    foreach ($allMonths as $mo) {
                        $sum[$metric] += $months[$mo];
                    }
                }
            }
            array_push($initArray, $init);
        }
        $responseArray = [];
        foreach ($saveArray as $array) {
            $response = $this->saveArray($array);
            array_push($responseArray, $response);
        }
        return $sum;
    }

    public function saveArray($array)
    {
        $check = [];
        $check['year'] = $array['year'] ?: $this->year;
        $check['metric'] = $array['metric'];
        $check['program_id'] = $array['program_id'];
        $check['channel'] = $array['channel'];
        $check['initiative'] = $array['initiative'];
        $check['month'] = $array['month'];

        $update = Forecast::updateOrCreate($check, $array);
        return $update;
    }



    public function expected()
    {
        $expected = new ForecastsExpected;
        return $expected->programChannels();
    }
    public function hasForecasts()
    {
        $expected = new ForecastsExpected;
        return $expected->hasForecasts();
    }

    // Current Forecasts for a list of programs / channel
    public function programs()
    {
        $filter = new Filter;
        $programs = $filter->programsList();
        $programIds = Program::whereIn('code', $programs)->pluck('id')->toArray();

        $current = Forecast::whereIn('program_id', $programIds)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();
        $time = new Time;
        $monthly = [];

        if (count($current) > 0) {
            foreach ($current as $c) {
                $metric = $c->metric;
                if (!array_key_exists($c->program, $monthly)) {
                    $monthly[$c->program] = [];
                }
                if (!array_key_exists($c->channel, $monthly[$c->program])) {
                    $monthly[$c->program][$c->channel] = new \StdClass();
                    $monthly[$c->program][$c->channel]->leads = new \StdClass();
                    $monthly[$c->program][$c->channel]->spend = new \StdClass();

                    $carbon = new Carbon($c->updated_at);
                    $monthly[$c->program][$c->channel]->updated = $carbon->toFormattedDateString();

                    if ($c->approved_at) {
                        $carbonApproved = new Carbon($c->approved_at);
                        $monthly[$c->program][$c->channel]->approved = $carbonApproved->toFormattedDateString();
                    } else {
                        $monthly[$c->program][$c->channel]->approved = null;
                    }

                    foreach ($time->allMonths as $mo) {
                        $monthly[$c->program][$c->channel]->leads->$mo = 0;
                        $monthly[$c->program][$c->channel]->spend->$mo = 0;
                    }
                }
                foreach ($time->allMonths as $mo) {
                    $monthly[$c->program][$c->channel]->$metric->$mo += $c->$mo;
                }
            }
        }

        return $monthly;
    }


    // Current Forecasts for a given program / channel
    public function program($program)
    {
        $programId = Program::where('code', $program)->pluck('id')->first();

        $current = Forecast::where('program_id', $programId)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        $time = new Time;
        $monthly = [];
        if (count($current) > 0) {
            foreach ($current as $c) {
                $metric = $c->metric;
                if (!array_key_exists($c->channel, $monthly)) {
                    $monthly[$c->channel] = new \StdClass();
                    $monthly[$c->channel]->leads = new \StdClass();
                    $monthly[$c->channel]->spend = new \StdClass();

                    $carbon = new Carbon($c->updated_at);
                    $monthly[$c->channel]->updated = $carbon->toFormattedDateString();

                    if ($c->approved_at) {
                        $carbonApproved = new Carbon($c->approved_at);
                        $monthly[$c->channel]->approved = $carbonApproved->toFormattedDateString();
                    } else {
                        $monthly[$c->channel]->approved = null;
                    }

                    foreach ($time->allMonths as $mo) {
                        $monthly[$c->channel]->leads->$mo = 0;
                        $monthly[$c->channel]->spend->$mo = 0;
                    }
                }
                foreach ($time->allMonths as $mo) {
                    $monthly[$c->channel]->$metric->$mo += $c->$mo;
                }
            }
        }

        return $monthly;
    }


    // Current Forecasts for a given program / channel
    public function programChannel($program, $channel)
    {
        $programId = Program::where('code', $program)->pluck('id')->first();
        
        $current = Forecast::where('program_id', $programId)
            ->where('channel', $channel)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        $time = new Time;
        $monthly = [];
        if (count($current) > 0) {
            foreach ($current as $c) {
                $metric = $c->metric;
                if (!array_key_exists($c->initiative, $monthly)) {
                    $monthly[$c->initiative] = new \StdClass();
                    $monthly[$c->initiative]->leads = new \StdClass();
                    $monthly[$c->initiative]->spend = new \StdClass();

                    $carbon = new Carbon($c->updated_at);
                    $monthly[$c->initiative]->updated = $carbon->toFormattedDateString();

                    if ($c->approved_at) {
                        $carbonApproved = new Carbon($c->approved_at);
                        $monthly[$c->initiative]->approved = $carbonApproved->toFormattedDateString();
                    } else {
                        $monthly[$c->initiative]->approved = null;
                    }

                    foreach ($time->allMonths as $mo) {
                        $monthly[$c->initiative]->leads->$mo = 0;
                        $monthly[$c->initiative]->spend->$mo = 0;
                    }
                }
                foreach ($time->allMonths as $mo) {
                    $monthly[$c->initiative]->$metric->$mo = $c->$mo;
                }
            }
        }

        return $monthly;
    }
}
