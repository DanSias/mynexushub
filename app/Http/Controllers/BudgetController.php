<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;

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
}
