<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Range;
use App\Helpers\Filter;
use App\Helpers\Metrics\Utilities;

use App\Models\CVRS;

// Performance Metrics
class Profitability
{
    public $filter;
    public $range;
    public $time;

    public $year;
    public $ltv;
    public $types = ['actuals', 'forecast', 'budget'];

    public function __construct()
    {
        $this->filter = new Filter;
        $this->range = new Range;
        $this->time = new Time;
        $this->year = $this->range->years()[0];

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
                $query[$type] = $query[$type]->orderBy('total', 'desc');
            }
            if (strpos($selectString, 'sum()') === false) {
                // The word was NOT found
                $query[$type] = $query[$type]->get();
            }
        }

        return $query;
    }

    // Base object for new group
    public function base($name = '')
    {
        $obj = new \StdClass();
        $obj->name = $name;
        $obj->leads = 0;
        $obj->spend = 0;
        $obj->leadsBudget = 0;
        $obj->spendBudget = 0;
        if (! empty($this->arraySubgroup())) {
            $subkey = $this->arraySubgroup();
            $obj->$subkey = [];
        }
        return $obj;
    }

    // Group Base object for adding from programs
    public function groupBase($name = '')
    {
        $obj = new \StdClass();
        $obj->name = $name;
        $obj->leads = 0;
        $obj->spend = 0;
        $obj->leadsBudget = 0;
        $obj->spendBudget = 0;
        $obj->starts = 0;
        $obj->startsBudget = 0;
        $obj->revenue = 0;
        $obj->revenueBudget = 0;

        return $obj;
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
                if ($q->total == 0) {
                    continue;
                }
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
                $subkey = $q->$arraySubgroup;

                // Create empty element if key is missing
                if (! array_key_exists($key, $array)) {
                    $array[$key] = $this->base($key);
                }
                $array[$key]->$metric += $q->total;

                // Organize by subgroup if requested
                if (! empty($arraySubgroup)) {
                    if (! array_key_exists($subkey, $array[$key]->$arraySubgroup)) {
                        $array[$key]->$arraySubgroup[$subkey] = $this->base($subkey);
                        // Unset duplicate subkey array
                        unset($array[$key]->$arraySubgroup[$subkey]->$arraySubgroup);
                    }
                    $array[$key]->$arraySubgroup[$subkey]->$metric += $q->total;
                }
            }
        }

        // Conversion Rates        
        if (! empty($arraySubgroup)) {
            $cvrs = $this->cvrs();
            foreach ($cvrs as $cvrsGroupKey => $cvrsSubkeys) {
                foreach ($cvrsSubkeys as $cvrsSubkey => $value) {
                    // Check that key exists before adding
                    if (array_key_exists($cvrsGroupKey, $array)) {
                        $array[$cvrsGroupKey]->$arraySubgroup[$cvrsSubkey]->cvrs = $value;
                        // Subgroup LTV
                        $array[$cvrsGroupKey]->$arraySubgroup[$cvrsSubkey]->ltv = $this->ltv[$cvrsSubkey];
                    }
                }
            }
        } else {
            $cvrsTotals = $this->cvrsTotals();
            foreach ($cvrsTotals as $key => $value) {
                if (array_key_exists($key, $array)) {
                    $array[$key]->cvrs = $value;
                }
            }
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

        // Get LTV for channel or initiative group
        if ($arrayGroup == 'channel' || $arrayGroup == 'initiative') {
            foreach ($array as $groupKey => $groupObject) {
                if ($groupobject->ltv == null) {
                    $groupObject->ltv = ($groupObject->starts > 0) ? $groupObject->revenue / $groupObject->starts : 0;
                }
            }
        }

        // For programs, channels, or initiatives - return current array
        if (in_array($this->filter->group, ['program', 'code', 'channel', 'initiative'])) {
            return $array;
        }

        // Other groups - build totals
        $groupArray = [];
        $metricsToSum = ['leads', 'spend', 'leadsBudget', 'spendBudget', 'starts', 'startsBudget', 'revenue', 'revenueBudget'];
        foreach ($array as $key => $value) {
            $programKey = $this->filter->keys[$key];
            if (! array_key_exists($programKey, $groupArray)) {
                $groupArray[$programKey] = $this->groupBase($programKey);
            }
            foreach ($metricsToSum as $metric) {
                $groupArray[$programKey]->$metric += $value->$metric;
            }
        }
        $groupArray = $this->calculateGroupProfit($groupArray);

        return $groupArray;
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

    public function calculateProfit($array)
    {
        $ltv = $this->ltv;

        foreach ($array as $key => $metrics) {
            // CPL
            $metrics->cpl = ($metrics->leads) ? $metrics->spend / $metrics->leads : 0;
            $metrics->cplBudget = ($metrics->leadsBudget) ? $metrics->spendBudget / $metrics->leadsBudget : 0;
            
            // Lifetime Value
            $metrics->ltv = $ltv[$key];

            // Starts
            $metrics->starts = $metrics->leads * $metrics->cvrs;
            $metrics->startsBudget = $metrics->leadsBudget * $metrics->cvrs;
            // Revenue
            $metrics->revenue = $metrics->starts * $metrics->ltv;
            $metrics->revenueBudget = $metrics->startsBudget * $metrics->ltv;
            // Cost per Start
            $metrics->cps = ($metrics->starts > 0) ? $metrics->spend / $metrics->starts : 0;
            $metrics->cpsBudget = ($metrics->startsBudget > 0) ? $metrics->spendBudget / $metrics->startsBudget : 0;
            // Margin
            $metrics->margin = $metrics->revenue - $metrics->spend;
            $metrics->marginBudget = $metrics->revenueBudget - $metrics->spendBudget;
            // MCOR
            $metrics->mcor = ($metrics->revenue > 0) ? $metrics->spend / $metrics->revenue : 0;
            $metrics->mcorBudget = ($metrics->revenueBudget > 0) ? $metrics->spendBudget / $metrics->revenueBudget : 0;
            // Ratio
            $metrics->ratio = ($metrics->mcor > 0) ? 1 / $metrics->mcor : 0;
            $metrics->ratioBudget = ($metrics->mcorBudget > 0) ? 1 / $metrics->mcorBudget : 0;
        }
        return $array;
    }

    public function calculateSubgroupProfit($array)
    {
        foreach ($array as $key => $metrics) {
            // Cost per Start
            $metrics->cps = ($metrics->starts > 0) ? $metrics->spend / $metrics->starts : 0;
            $metrics->cpsBudget = ($metrics->startsBudget > 0) ? $metrics->spendBudget / $metrics->startsBudget : 0;
            // Margin
            $metrics->margin = $metrics->revenue - $metrics->spend;
            $metrics->marginBudget = $metrics->revenueBudget - $metrics->spendBudget;
            // MCOR
            $metrics->mcor = ($metrics->revenue > 0) ? $metrics->spend / $metrics->revenue : 0;
            $metrics->mcorBudget = ($metrics->revenueBudget > 0) ? $metrics->spendBudget / $metrics->revenueBudget : 0;
            // Ratio
            $metrics->ratio = ($metrics->mcor > 0) ? 1 / $metrics->mcor : 0;
            $metrics->ratioBudget = ($metrics->mcorBudget > 0) ? 1 / $metrics->mcorBudget : 0;

            $drop = $this->arraySubgroup();
            unset($metrics->$drop);
        }
        return $array;
    }

    public function calculateGroupProfit($array)
    {
        foreach ($array as $key => $metrics) {
            $metrics->cps = ($metrics->starts > 0) ? $metrics->spend / $metrics->starts : 0;
            $metrics->cpsBudget = ($metrics->startsBudget > 0) ? $metrics->spendBudget / $metrics->startsBudget : 0;
            // Margin
            $metrics->margin = $metrics->revenue - $metrics->spend;
            $metrics->marginBudget = $metrics->revenueBudget - $metrics->spendBudget;
            // MCOR
            $metrics->mcor = ($metrics->revenue > 0) ? $metrics->spend / $metrics->revenue : 0;
            $metrics->mcorBudget = ($metrics->revenueBudget > 0) ? $metrics->spendBudget / $metrics->revenueBudget : 0;
            // Ratio
            $metrics->ratio = ($metrics->mcor > 0) ? 1 / $metrics->mcor : 0;
            $metrics->ratioBudget = ($metrics->mcorBudget > 0) ? 1 / $metrics->mcorBudget : 0;
            // LTV
            $metrics->ltv = ($metrics->starts > 0) ? $metrics->revenue / $metrics->starts : 0;
            // CPL
            $metrics->cpl = ($metrics->leads) ? $metrics->spend / $metrics->leads : 0;
            $metrics->cplBudget = ($metrics->leadsBudget) ? $metrics->spendBudget / $metrics->leadsBudget : 0;
            // CVRS
            $metrics->cvrs = ($metrics->leads > 0) ? $metrics->starts / $metrics->leads : 0;
            $metrics->cvrsBudget = ($metrics->leadsBudget > 0) ? $metrics->startsBudget / $metrics->leadsBudget : 0;
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
            $complete = $this->time->allMonths();
            $remaining = [];
        }
        $completeString = implode(' + ', $complete);
        $completeString = rtrim($completeString, ' + ');

        $remainingString = implode(' + ', $remaining);
        $remainingString = rtrim($remainingString, ' + ');

        if ($this->filter->group == 'initiative') {
            $rawString = 'program, channel, initiative, metric, sum(';
        } else {
            $rawString = 'program, channel, metric, sum(';
        }

        $strings['actuals'] = $rawString . $completeString . ') as total';
        $strings['forecast'] = $rawString . $remainingString . ') as total';
        $strings['budget'] = 'program, channel, initiative, metric, sum(january + february + march + april + may + june + july + august + september + october + november + december) as total';

        return $strings;
    }

    // Utility Methods

    public function arrayGroup()
    {
        switch ($this->filter->group) {
            case 'program':
            case 'code':
            case 'location':
            case 'level':
            case 'bu':
            case 'vertical':
            case 'subvertical':
            case 'school':
            case 'partner':
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
