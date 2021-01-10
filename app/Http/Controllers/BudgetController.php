<?php

namespace App\Http\Controllers;

use Auth;
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


    // Save Settings
    public function saveSettings(Request $request)
    {
        $type = 'budget';
        $settings = $request->input('settings');
        $returnArray = [];

        $user = Auth::user();
        $id = $user->id;

        foreach ($settings as $key => $value) {
            $array = [];
            $array['type'] = $type;
            $array['key'] = $key;
            $array['value'] = $value;
            $array['user_id'] = $id;

            $check['type'] = $type;
            $check['key'] = $key;
            $save = Setting::updateOrCreate($check, $array);
            // array_push($returnArray, $save);
        }
        $current = Setting::where('type', 'budget')->get();
        return $current;
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
