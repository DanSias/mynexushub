<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Range;
use App\Helpers\Filter;
use App\Helpers\Metrics\Utilities;

use App\Models\CVRS;

// Performance Metrics
class ProfitabilityMonthly
{
    public $filter;
    public $range;
    public $time;

    public $year;
    public $months;
    public $ltv;
    public $types = ['actuals', 'forecast', 'budget'];

    public function __construct()
    {
        $this->filter = new Filter;
        $this->range = new Range;
        $this->time = new Time;
        $this->year = $this->range->years()[0];
        $this->months = $this->time->allMonths;
        $utilities = new Utilities;
        $this->ltv = $utilities->lifetimeValues($this->filter->programsList());
    }

    // Metrics that need to be added in the subgroup
    public $sumMetrics = ['starts', 'startsBudget', 'revenue', 'revenueBudget'];

    public $profitabilityChannels = [
        'SEO',
        'SEO2',
        'Referral',
        'Paid Social',
        'Paid Search',
        'Portals',
        'Email',
        'Internet Advertising',
        'Conversica',
        'Application',
        'Chat',
        'Inbound Call',
        'print',
        'Radio',
        'Television',
        'Creative',
        'Cultivation',
        'Outreach',
        'Partnerships',
        'Corporate Partnerships',
        'MARP',
        'Mail',
    ];

    public $profitabilityInitiatives = [
        'Paid Search' => [
            'BING',
            'GOOGLE',
        ],
        'Paid Social' => [
            'FACEBOOK',
            'FBLEADAD',
            'LINKEDIN',
            'INSTAGRAM'
        ],
        'Portals' => [
            'COLEGEDEGREE',
            'ELN',
            'OMBA',
            'QST',
            'SCL'
        ]
    ];

    // Build query for all table types
    public function query()
    {
        foreach ($this->types as $type) {
            $table = 'monthly_' . $type . '_' . $this->year;
            $selectString = $this->timeStrings()[$type];

            $query[$type] = DB::table($table)
                ->select(DB::raw($selectString))
                ->whereIn('program', $this->filter->programsList())
                ->whereNotIn('program', ['#N/A', ''])
                ->where('metric', '!=', 'CPL')
                ->groupBy('metric')
                ->groupBy('channel')
                ->groupBy('program');
            
            if ($this->filter->channel && count($this->filter->channel) > 0) {
                $query[$type] = $query[$type]->whereIn('channel', $this->filter->channel);
            }

            // Channel and / or initiataive grouping
            if ($this->filter->group == 'initiative') {
                $query[$type] = $query[$type]->groupBy('initiative');
            }

            // Conversica
            if ($this->filter->conversica == false) {
                $query[$type] = $query[$type]->where('initiative', 'not like', '%CONVERSICA%');
            }

            // Forecast order by 
            if ($this->filter->metric == 'forecast') {
                $query[$type] = $query[$type]->orderBy('january', 'desc');
            }

            $query[$type] = $query[$type]->get();
        }

        return $query;
    }

    // Base object for new group
    public function base($name = '')
    {
        $obj = new \StdClass();
        $obj->name = $name;
        $obj->leads = $this->baseMonthlyArray();
        $obj->spend = $this->baseMonthlyArray();
        $obj->leadsBudget = $this->baseMonthlyArray();
        $obj->spendBudget = $this->baseMonthlyArray();
        if (! empty($this->arraySubgroup())) {
            $subkey = $this->arraySubgroup();
            $obj->$subkey = [];
        }
        return $obj;
    }

    public function baseMonthlyArray()
    {
        $array = [];
        foreach ($this->time->allMonths as $mo) {
            $array[$mo] = 0;
        }
        return $array;
    }

    public function profitability()
    {
        // Fetch data from DB tables
        $query = $this->query();
        $array = [];
        $arrayGroup = $this->arrayGroup();
        $arraySubgroup = $this->arraySubgroup();

        // Loop through each table type in query
        foreach ($this->types as $type) {
            foreach ($query[$type] as $q) {
                // Rollup ASU programs to ASU-BRAND
                if (strpos($q->program, 'ASU-') !== false) {
                    $q->program = 'ASU-BRAND';
                }

                // Categorize by metric
                $metric = strtolower($q->metric);
                if ($type == 'budget') {
                    $metric .= 'Budget';
                }

                // Channel in Expected Array
                $channel = $q->channel;
                if (!in_array($channel, $this->profitabilityChannels)) {
                    $channel = 'All Others';
                }

                // Initiatives with CVRS or "Channel Other
                if ($arrayGroup == 'initiative' && $this->filter->metric != 'forecast') {
                    if (array_key_exists($channel, $this->profitabilityInitiatives)) {
                        $list = $this->profitabilityInitiatives[$channel];
                        if (! in_array($q->initiative, $list)) {
                            $q->initiative = $channel . ' Other';
                        }
                    }
                }

                // Organize by group
                $key = $q->$arrayGroup;
                $subkey = ($arraySubgroup) ? $q->$arraySubgroup : '';

                // Create empty element if key is missing
                if (! array_key_exists($key, $array)) {
                    $array[$key] = $this->base($key);
                }
                foreach ($this->months as $mo) {
                    if (property_exists($q, $mo) && $q->$mo > 0) {
                        $array[$key]->$metric[$mo] += $q->$mo;
                    }
                }

                // Organize by subgroup if requested
                if (! empty($arraySubgroup)) {
                    if (! array_key_exists($subkey, $array[$key]->$arraySubgroup)) {
                        $array[$key]->$arraySubgroup[$subkey] = $this->base($subkey);
                        // Unset duplicate subkey array
                        unset($array[$key]->$arraySubgroup[$subkey]->$arraySubgroup);
                    }
                    foreach ($this->months as $mo) {
                        if (property_exists($q, $mo) && $q->$mo > 0) {
                            $array[$key]->$arraySubgroup[$subkey]->$metric[$mo] += $q->$mo;
                        }
                    }
                }
            }
        }

        // Conversion Rates        
        if (! empty($arraySubgroup)) {
            $cvrs = $this->cvrs();
            // foreach ($cvrs as $cvrsGroupKey => $cvrsSubkeys) {
            //     foreach ($cvrsSubkeys as $cvrsSubkey => $value) {
            //         // Check that key exists before adding
            //         if (array_key_exists($cvrsGroupKey, $array)) {
            //             $array[$cvrsGroupKey]->$arraySubgroup[$cvrsSubkey]->cvrs = $value;
            //             // Subgroup LTV
            //             $array[$cvrsGroupKey]->$arraySubgroup[$cvrsSubkey]->ltv = $this->ltv[$cvrsSubkey];
            //         }
            //     }
            // }
        } else {
            $cvrsTotals = $this->cvrsTotals();
            foreach ($cvrsTotals as $key => $value) {
                if (array_key_exists($key, $array)) {
                    $array[$key]->cvrs = $value;
                }
            }
        }

        if ($this->filter->metric == 'forecast') {
            $array = $this->calculateCpl($array);
            return $array;
        }

        // Calculate extended metrics for each group and subgroup
        $array = $this->calculateProfit($array);
        if (! empty($arraySubgroup)) {
            foreach ($array as $groupKey => $groupObject) {
                $groupObject->$arraySubgroup = $this->calculateProfit($groupObject->$arraySubgroup);
            }
        }

        // For channel or initiative group, add subgroup totals since there is no LTV
        if ($arrayGroup == 'channel' || $arrayGroup == 'initiative') {
            foreach ($array as $groupKey => $groupObject) {
                $sum = $this->subgroupTotals($groupObject->$arraySubgroup);
                foreach ($this->sumMetrics as $metric) {
                    $array[$groupKey]->$metric = $sum[$metric];
                }
                $array[$groupKey]->cvrs = ($array[$groupKey]->leads > 0) ? $array[$groupKey]->starts / $array[$groupKey]->leads : 0;
            }
            $array = $this->calculateSubgroupProfit($array);
        }

        return $array;
    }

    public function subgroupTotals($array)
    {
        $arraySum = [];
        foreach ($this->sumMetrics as $metric) {
            $arraySum[$metric] = 0;
        }
        foreach ($array as $item => $value) {
            foreach ($this->sumMetrics as $metric) {
                $arraySum[$metric] += $value->$metric;
            }
        }
        return $arraySum;
    }

    public function calculateCpl($array)
    {
        foreach ($array as $key => $metrics) {
            $sum = 0;
            foreach ($this->months as $mo) {
                // CPL
                $metrics->spend[$mo] = round($metrics->spend[$mo]);
                $metrics->cpl[$mo] = ($metrics->leads[$mo]) ? $metrics->spend[$mo] / $metrics->leads[$mo] : 0;
                $metrics->cplBudget[$mo] = ($metrics->leadsBudget[$mo]) ? $metrics->spendBudget[$mo] / $metrics->leadsBudget[$mo] : 0;
                // Check values
                $sum += $metrics->leads[$mo];
                $sum += $metrics->leadsBudget[$mo];
                $sum += $metrics->spend[$mo];
                $sum += $metrics->spendBudget[$mo];
            }
            if ($sum == 0) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    public function calculateProfit($array)
    {
        $ltv = $this->ltv;

        foreach ($array as $key => $metrics) {
            foreach ($this->months as $mo) {
                // CPL
                $metrics->cpl[$mo] = ($metrics->leads[$mo]) ? $metrics->spend[$mo] / $metrics->leads[$mo] : 0;
                $metrics->cplBudget[$mo] = ($metrics->leadsBudget[$mo]) ? $metrics->spendBudget[$mo] / $metrics->leadsBudget[$mo] : 0;
            
                // Lifetime Value
                $metrics->ltv = $ltv[$key];

                // Starts
                $metrics->starts[$mo] = $metrics->leads[$mo] * $metrics->cvrs;
                $metrics->startsBudget[$mo] = $metrics->leadsBudget[$mo] * $metrics->cvrs;
                // Revenue
                $metrics->revenue[$mo] = $metrics->starts[$mo] * $metrics->ltv;
                $metrics->revenueBudget[$mo] = $metrics->startsBudget[$mo] * $metrics->ltv;
                // Cost per Start
                $metrics->cps[$mo] = ($metrics->starts[$mo] > 0) ? $metrics->spend[$mo] / $metrics->starts[$mo] : 0;
                $metrics->cpsBudget[$mo] = ($metrics->startsBudget[$mo] > 0) ? $metrics->spendBudget[$mo] / $metrics->startsBudget[$mo] : 0;
                // Margin
                $metrics->margin[$mo] = $metrics->revenue[$mo] - $metrics->spend[$mo];
                $metrics->marginBudget[$mo] = $metrics->revenueBudget[$mo] - $metrics->spendBudget[$mo];
                // MCOR
                $metrics->mcor[$mo] = ($metrics->revenue[$mo] > 0) ? $metrics->spend[$mo] / $metrics->revenue[$mo] : 0;
                $metrics->mcorBudget[$mo] = ($metrics->revenueBudget[$mo] > 0) ? $metrics->spendBudget[$mo] / $metrics->revenueBudget[$mo] : 0;
                // Ratio
                $metrics->ratio[$mo] = ($metrics->mcor[$mo] > 0) ? 1 / $metrics->mcor[$mo] : 0;
                $metrics->ratioBudget[$mo] = ($metrics->mcorBudget[$mo] > 0) ? 1 / $metrics->mcorBudget[$mo] : 0;
            }
        }
        return $array;
    }

    public function calculateSubgroupProfit($array)
    {
        foreach ($array as $key => $metrics) {
            foreach ($this->months as $mo) {
                // Cost per Start
                $metrics->cps[$mo] = ($metrics->starts[$mo] > 0) ? $metrics->spend[$mo] / $metrics->starts[$mo] : 0;
                $metrics->cpsBudget[$mo] = ($metrics->startsBudget[$mo] > 0) ? $metrics->spendBudget[$mo] / $metrics->startsBudget[$mo] : 0;
                // Margin
                $metrics->margin[$mo] = $metrics->revenue[$mo] - $metrics->spend[$mo];
                $metrics->marginBudget[$mo] = $metrics->revenueBudget[$mo] - $metrics->spendBudget[$mo];
                // MCOR
                $metrics->mcor[$mo] = ($metrics->revenue[$mo] > 0) ? $metrics->spend[$mo] / $metrics->revenue[$mo] : 0;
                $metrics->mcorBudget[$mo] = ($metrics->revenueBudget[$mo] > 0) ? $metrics->spendBudget[$mo] / $metrics->revenueBudget[$mo] : 0;
                // Ratio
                $metrics->ratio[$mo] = ($metrics->mcor[$mo] > 0) ? 1 / $metrics->mcor[$mo] : 0;
                $metrics->ratioBudget[$mo] = ($metrics->mcorBudget[$mo] > 0) ? 1 / $metrics->mcorBudget[$mo] : 0;

                $drop = $this->arraySubgroup();
                unset($metrics->$drop);
            }
        }
        return $array;
    }
    
    // Conversion Rates (lead to start)
    public function cvrs()
    {
        $cvrs = CVRS::where('year', $this->year)
            ->where('cvrs', '>', 0)
            ->whereIn('program', $this->filter->programsList());

        if ($this->filter->channel && count($this->filter->channel) > 0) {
            $cvrs = $cvrs->whereIn('channel', $this->filter->channel);
        }

        $cvrs = $cvrs->get();
        
        $array = [];
        $arrayGroup = $this->arrayGroup();
        $arraySubgroup = $this->arraySubgroup();
        
        foreach ($cvrs as $c) {
            // If initiative string does not contain the channel, uppercase
            if ($arrayGroup == 'initiative') {
                if (strpos($c->initiative, $c->channel) === false) {
                    $c->initiative = strtoupper($c->initiative);
                }
            }
            $array[$c->$arrayGroup][$c->$arraySubgroup] = $c->cvrs;
        }
        return $array;
    }

    public function cvrsTotals()
    {
        $cvrs = CVRS::where('channel', 'Program Total')
            ->where('year', $this->year)
            ->get(['year', 'program', 'cvrs']);
        $array = [];
        foreach ($cvrs as $c) {
            $array[$c->program] = $c->cvrs;
        }
        return $array;
    }


    // String to query for complete Months
    public function timeStrings()
    {
        $year = $this->range->years()[0];
        $calendarYear = date("Y");
        if ($year == $calendarYear) {
            $complete = $this->time->completeMonths();
            $remaining = $this->time->remainingMonths();
        } else {
            $complete = $this->time->allMonths;
            $remaining = [];
        }
        // Complete Months
        $completeString = '';
        foreach ($complete as $c) {
            $completeString .= 'SUM(' . $c . ') as ' . $c . ', ';
        }
        $completeString = rtrim($completeString, ', ');

        // Remaining Months
        $remainingString = '';
        foreach ($remaining as $r) {
            $remainingString .= 'SUM(' . $r . ') as ' . $r . ', ';
        }
        $remainingString = rtrim($remainingString, ', ');

        // All Months
        $allString = '';
        foreach ($this->months as $m) {
            $allString .= 'SUM(' . $m . ') as ' . $m . ', ';
        }
        $allString = rtrim($allString, ', ');

        if ($this->filter->group == 'initiative') {
            $rawString = 'program, channel, initiative, metric, ';
        } else {
            $rawString = 'program, channel, metric, ';
        }

        $strings['actuals'] = $rawString . $completeString;
        $strings['forecast'] = $rawString . $remainingString;
        $strings['budget'] = $rawString . $allString;

        return $strings;
    }

    // Utility Methods

    public function arrayGroup()
    {
        switch ($this->filter->group) {
            case 'program':
            case 'code':
                return 'program';
                break;
            
            default:
                return $this->filter->group;
                break;
        }
    }

    public function arraySubgroup()
    {
        switch ($this->filter->subgroup) {
            case 'program':
            case 'code':
                return 'program';
                break;
            
            default:
                return $this->filter->subgroup;
                break;
        }
    }
}
