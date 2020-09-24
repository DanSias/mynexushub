<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;
use App\Helpers\Metrics\BudgetesExpected;

class BudgetController extends Controller
{
    // Budget Settings
    public $scenario;
    public $status;
    public $year;
    
    public function __construct()
    {
        $settings = Setting::where('type', 'budget')->get();
        foreach ($settings as $setting) {
            $key = $setting->key;
            $value = $setting->value;
            $this->$key = $value;
        }
    }

    public function index($program = '', $channel = '')
    {
        return Inertia::render('Budget', 
            [
                'year' => (int) $this->year,
                'scenario' => $this->scenario, 
                'status' => $this->status, 
                'program' => $program,
                'channel' => $channel
            ]);
    }

    public function settings()
    {
        return [
            'year' => $this->year,
            'scenario' => $this->scenario,
            'status' => $this->status
        ];
    }




    public function expected()
    {
        $expected = new BudgetsExpected;
        return $expected->programChannels();
    }
    public function hasForecasts()
    {
        $expected = new BudgetsExpected;
        return $expected->hasForecasts();
    }
}
