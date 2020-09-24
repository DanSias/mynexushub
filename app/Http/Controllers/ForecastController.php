<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Models\Setting;
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
        return Inertia::render('Forecast', 
            [
                'year' => (int) $this->year,
                'month' => $this->month, 
                'status' => $this->status, 
                'program' => $program,
                'channel' => $channel
            ]);
    }

    public function settings()
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'status' => $this->status
        ];
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
}
