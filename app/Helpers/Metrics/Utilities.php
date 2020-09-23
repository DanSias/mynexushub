<?php

namespace App\Helpers\Metrics;

use Illuminate\Support\Facades\DB;

use App\Helpers\Time;

class Utilities
{
    // All channels for a given table type   
    public function allChannels($type = 'actuals')
    {
        $time = new Time;
        $table = 'monthly_' . $type . '_' . $time->getYear();

        $channels = DB::table($table)
            ->where('channel', '!=', '')
            ->orderBy('channel', 'asc')
            ->distinct('channel')
            ->pluck('channel');
        return $channels->values();
    }

    // All Initiatives in a given channel
    public function channelInitiatives($channel = 'Paid Search', $type = 'actuals')
    {
        $time = new Time;
        $table = 'monthly_' . $type . '_' . $time->getYear();

        if (is_array($channel)) {
            $initiatives = DB::table($table)
                ->whereIn('channel', $channel)
                ->orderBy('initiative', 'asc')
                ->distinct('initiative')
                ->pluck('initiative');
        } else {
            $initiatives = DB::table($table)
                ->where('channel', $channel)
                ->orderBy('initiative', 'asc')
                ->distinct('initiative')
                ->pluck('initiative');
        }
        
        $array = $initiatives->values();
        return $array;
    }
    public function allInitiatives($channel = '', $type = 'actuals')
    {
        $table = '';
        $time = new Time;
        if ($type == 'actuals') {
            $table = 'monthly_actuals_' . $time->getYear();
        }
        if ($channel == '') {
            $initiatives = DB::table($table)
                ->orderBy('initiative', 'asc')
                ->distinct('initiative')
                ->pluck('initiative');
        }
        elseif (is_array($channel)) {
            $initiatives = DB::table($table)
                ->whereIn('channel', $channel)
                ->orderBy('initiative', 'asc')
                ->distinct('initiative')
                ->pluck('initiative');
        } else {
            $initiatives = DB::table($table)
                ->where('channel', $channel)
                ->orderBy('initiative', 'asc')
                ->distinct('initiative')
                ->pluck('initiative');
        }
        
        $array = $initiatives->values();
        return $array;
    }

    // Lifetime Values for given array of programs
    public function lifetimeValues($programArray = [])
    {
        $ltvQuery = DB::table('programs');
        if (count($programArray) > 0) {
            $ltvQuery = $ltvQuery->whereIn('code', $programArray);
        }
        $ltvQuery = $ltvQuery->get(['code', 'ltv']);
        $ltvArray = [];
        foreach ($ltvQuery as $ltv) {
            $ltvArray[$ltv->code] = $ltv->ltv;
        }
        return $ltvArray;
    }
}
