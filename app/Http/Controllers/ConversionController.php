<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CVRS;

use App\Helpers\vTime;

class ConversionController extends Controller
{
    // Merge Data
    public function merge()
    {
        $time = new Time;
        $year = $time->getYear();
        
        $programs = CVRS::distinct('program')->pluck('program');

        $cvrs = CVRS::where('year', $year)
            ->where('cvrs', '>', 0)
            ->get(['program', 'channel', 'initiative', 'cvrs']);

        $rates = [];
        foreach ($cvrs as $c) {
            $program = $c->program;
            $channel = $c->channel;
            $initiative = $c->initiative;
            if (! array_key_exists($program, $rates)) {
                $rates[$program] = [];
            }
            if (! array_key_exists($channel, $rates[$program])) {
                $rates[$program][$channel] = [];
            }
            // Initiative level for Search, Social, Portals
            if ($channel == 'Paid Search' || $channel == 'Paid Social' || $channel == 'Portals') {
                $rates[$program][$channel][$initiative] = $c->cvrs;
            } else {
                $rates[$program][$channel] = $c->cvrs;
            }
        }

        // Initiatives for which a CVRS exists
        $focusInitiatives = [
            'GOOGLE' => 'Google',
            'BING' => 'Bing',
            'FACEBOOK' => 'Facebook',
            'FBLEADAD' => 'FBLEADAD',
            'LINKEDIN' => 'LinkedIN',
            'INSTAGRAM' => 'Instagram',
            'COLLEGEDEGREE' => 'CollegeDegree',
            'ELN' => 'ELN',
            'OMBA' => 'OMBA',
            'QST' => 'QST',
            'SCL' => 'SCL',
        ];

        $complete = $time->completeMonths();
        $completeString = implode(' + ', $complete);
        $completeString = rtrim($completeString, ' + ');

        $remaining = $time->remainingMonths();
        $remainingString = implode(' + ', $remaining);
        $remainingString = rtrim($remainingString, ' + ');


        $rawString = 'program, channel, initiative, sum(';

        $rawActualsString = $rawString . $completeString . ') as total';
        $rawForecastString = $rawString . $remainingString . ') as total';

        if ($completeString != '') {
            $query['actuals'] = DB::table('monthly_actuals_' . $year)
                ->select(DB::raw($rawActualsString))
                ->where('metric', 'Leads')
                ->groupBy('program')
                ->groupBy('channel')
                ->groupBy('initiative')
                ->get();
        }
        
        $query['forecasts'] = DB::table('monthly_forecast_' . $year)
            ->select(DB::raw($rawForecastString))
            ->where('metric', 'Leads')
            ->groupBy('program')
            ->groupBy('channel')
            ->groupBy('initiative')
            ->get();

        $leads = [];
        $baseChannels = $details->cvrsChannels();

        foreach ($query as $key => $value) {
            foreach($value as $item => $a) {
                // Skip if no leads
                if ($a->total == 0) {
                    continue;
                }
                $program = $a->program;
                $channel = $a->channel;
                $initiative = $a->initiative;

                // Check for initiative in focus
                if (array_key_exists($initiative, $focusInitiatives)) {
                    $initiative = $focusInitiatives[$initiative];
                } else {
                    $initiative = $channel . ' Other';
                }

                // Roll Up ASU
                if (strpos($program, 'ASU-') !== false) {
                    $program = 'ASU-BRAND';
                }
                // Check All Others channel
                if (! in_array($channel, $baseChannels)) {
                    $channel = 'All Others';
                }

                // Check for array structure (program and channel)
                if (! array_key_exists($program, $leads)) {
                    $leads[$program] = [];
                }
                if (! array_key_exists($channel, $leads[$program])) {
                    if ($channel == 'Paid Search' || $channel == 'Paid Social' || $channel == 'Portals') {
                        $leads[$program][$channel] = [];
                    } else {
                        $leads[$program][$channel] = 0;
                    }
                }
                // Add initiative rows to required channels
                if ($channel == 'Paid Search' || $channel == 'Paid Social' || $channel == 'Portals') {
                    if (! array_key_exists($initiative, $leads[$program][$channel])) {
                        $leads[$program][$channel][$initiative] = 0;
                    }
                }
                // Add to totals
                if ($channel == 'Paid Search' || $channel == 'Paid Social' || $channel == 'Portals') {
                    $leads[$program][$channel][$initiative] += $a->total;
                } else {
                    $leads[$program][$channel] += $a->total;
                }
            }
        }
        $totalChannels = [
            'SEO',
            'SEO2',
            'Referral',
            'Email',
            'Internet Advertising',
            'Conversica',
            'Application',
            'Chat',
            'Inbound Call',
            'Print',
            'Radio',
            'Television',
            'Creative',
            'Cultivation',
            'Outreach',
            'Partnerships',
            'Mail',
            'All Others',
        ];
        $initiativeChannels = ['Paid Search', 'Paid Social', 'Portals'];
        $allChannels = array_merge($totalChannels, $initiativeChannels);

        // Initiative keys by channel
        $initiativeKeys['Paid Search'] = [
            'Google',
            'Bing',
            'Paid Search Other'
        ];
        $initiativeKeys['Paid Social'] = [
            'Facebook',
            'FBLEADAD',
            'LinkedIN',
            'Instagram',
            'Paid Social Other',
        ];
        $initiativeKeys['Portals'] = [
            'CollegeDegree',
            'ELN',
            'OMBA',
            'QST',
            'SCL',
            'Portals Other',
        ];

        // Loop through all programs
        $programSum = [];
        foreach ($programs as $program) {
            $programSum[$program] = [];
            // Proceed if programs has leads AND conversion rates
            if (array_key_exists($program, $rates) && array_key_exists($program, $leads)) {
                $rateData = $rates[$program];
                $programData = $leads[$program];
                $pData['rate'] = $rateData;
                $pData['program'] = $programData; 

                // Loop through paid & portal channels to get initiative totals
                foreach ($initiativeChannels as $channel) {
                    $sum['leads'] = 0;
                    $sum['starts'] = 0;
                    $keys = $initiativeKeys[$channel];
                    // Object for updating DB
                    $sum['object'] = new \StdClass();
                    $sum['object']->year = $year;
                    $sum['object']->program = $program;
                    $sum['object']->channel = $channel;
                    $sum['object']->initiative = $channel . ' Total';

                    foreach ($keys as $initiative) {
                        if (array_key_exists($channel, $programData) && array_key_exists($initiative, $programData[$channel])) {
                            $sum['leads'] += $programData[$channel][$initiative];
                        }
                        if (array_key_exists($channel, $rateData) && array_key_exists($initiative, $rateData[$channel])) {
                            if (array_key_exists($channel, $programData) && array_key_exists($initiative, $programData[$channel])) {
                                $sum['starts'] += $programData[$channel][$initiative] * $rateData[$channel][$initiative];
                            }
                        }
                    }
                    $sum['cvrs'] = ($sum['leads'] > 0) ? $sum['starts'] / $sum['leads'] : 0;
                    $sum['object']->leads = $sum['leads'];
                    $sum['object']->starts = $sum['starts'];
                    $sum['object']->cvrs = $sum['cvrs'];

                    $update = $this->updateCvrs($sum['object']);

                    // Add to programSum
                    $programSum[$program][$channel] = $sum['object'];
                }

                // Loop through remaining channels for channel totals
                foreach ($totalChannels as $channel) {
                    $sum['leads'] = 0;
                    $sum['starts'] = 0;

                    // Object for updating DB
                    $sum['object'] = new \StdClass();
                    $sum['object']->year = $year;
                    $sum['object']->program = $program;
                    $sum['object']->channel = $channel;
                    $sum['object']->initiative = $channel;

                    // dd($programData);
                    if (array_key_exists($channel, $programData)) {
                        $sum['leads'] += $programData[$channel];
                    }
                    if (array_key_exists($channel, $rateData)) {
                        $sum['starts'] += $sum['leads'] * $rateData[$channel];
                    }
                    
                    $sum['cvrs'] = ($sum['leads'] > 0) ? $sum['starts'] / $sum['leads'] : 0;
                    $sum['object']->leads = $sum['leads'];
                    $sum['object']->starts = $sum['starts'];
                    $sum['object']->cvrs = $sum['cvrs'];

                    // Add to programSum
                    $programSum[$program][$channel] = $sum['object'];
                }

                // Create object for the program total
                $programSum[$program]['Program Total'] = new \StdClass();
                $programSum[$program]['Program Total']->year = $year;
                $programSum[$program]['Program Total']->program = $program;
                $programSum[$program]['Program Total']->channel = 'Program Total';
                $programSum[$program]['Program Total']->initiative = 'All';
                $programSum[$program]['Program Total']->leads = 0;
                $programSum[$program]['Program Total']->starts = 0;
                $programSum[$program]['Program Total']->cvrs = 0;
                
                // Loop through all channels to get the program total
                foreach ($allChannels as $channel) {
                    $programSum[$program]['Program Total']->leads += $programSum[$program][$channel]->leads;
                    $programSum[$program]['Program Total']->starts += $programSum[$program][$channel]->starts;
                }
                $programSum[$program]['Program Total']->cvrs = ($programSum[$program]['Program Total']->leads > 0) ? $programSum[$program]['Program Total']->starts / $programSum[$program]['Program Total']->leads: 0;   

                // Update Pprogram Total
                $update = $this->updateCvrs($programSum[$program]['Program Total']);
            }
        }

        // Return view with table
        return view('merge.cvrs', compact('programSum', 'programs', 'allChannels'));
    }

}
