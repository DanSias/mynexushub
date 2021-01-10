<?php

namespace App\Helpers\Metrics;

use App\Models\ConversicaFunnel as ConversicaFullFunnel;
use App\Models\Program;

use Illuminate\Support\Facades\DB;

class ConversicaFunnel
{
    public $filter;
    public $programs;
    public $attributes;
    public $group;
    public $keys;
    public $funnelInitiative;

    public $metrics = [
        'leads',
        'hot',
        'engaged',
        'contact',
        'first_contact_after_conversion15',
        'quality_after_conversion',
        'quality_after_conversion30',
        'app',
        'starts',
        'total_leads'
    ];

    public $properties = [
        'school',
        'program',
        'year',
        'month'
    ];

    public function __construct($filter = [])
    {
        if ($filter != []) {
            $this->filter = $filter;
            $this->group = strtolower($filter->group);
            $this->programs = $filter->programsList();
            $this->funnelInitiative = (property_exists($filter, 'funnelInitiative')) ? $filter->funnelInitiative : '';
        }
        $this->allPrograms();
    }

    public function allPrograms()
    {
        $codes = DB::table('conversica_funnel')
            ->distinct()
            ->where('program', '!=', 'Others')
            ->get(['program']);
        $array = [];
        foreach ($codes as $c) {
            array_push($array, str_replace('_', '-', $c->program));
        }
        $programs = Program::all();
        $this->attributes = $programs;
        return json_encode($programs);
    }


    // To Merge with Metrics
    public function query()
    {
        $query = ConversicaFullFunnel::query();
        $query->selectRaw($this->queryString());

        $query = $query->groupBy('program');
        $query = $query->groupBy('year');
        $query = $query->groupBy('month');


        return $query;
    }

    public function count()
    {
        $query = ConversicaFullFunnel::query();
        // $query->selectRaw($this->queryString());
        $query = $query->whereIn('program', $this->programs);
        $query = $this->checkInitiative($query);
        $query = $query->get(['program', 'year', 'month']);

        $query = $this->addKeys($query);

        $returnArray = [];

        foreach ($query as $c) {
            $key = $c->key;

            if (!array_key_exists($key, $returnArray)) {
                $returnArray[$key] = $this->createElement($key, 'deployments');
            }

            $yearMonth = $this->timeIndex($c);

            if (substr($yearMonth, 0, 4) >= 2017) {
                $returnArray[$key]->deployments[$yearMonth] += 1;
            }
        }

        return $returnArray;
    }

    // Build string to query 
    public function queryString()
    {
        $selectString = implode(', ', $this->properties);
        $selectString .= ', ';

        foreach ($this->metrics as $m) {
            $selectString .= 'sum(' . $m . ') as ' . $m . ', ';
        }
        $selectString = rtrim($selectString, ', ');

        return $selectString;
    }

    // Query Table to return relevant rows
    public function actuals()
    {
        // Columns to pull from the DB
        $properties = $this->metrics;

        $query = $this->query();
        $query = $query->whereIn('program', $this->programs);
        $query = $this->checkInitiative($query);
        $query = $query->get();

        $query = $this->addKeys($query);

        $returnArray = [];

        foreach ($query as $c) {
            $key = $c->key;

            if (!array_key_exists($key, $returnArray)) {
                $returnArray[$key] = $this->createElement($key);
            }

            $yearMonth = $this->timeIndex($c);

            // Add Monthly Values to all properties
            foreach ($properties as $prop) {
                if (substr($yearMonth, 0, 4) >= 2017) {
                    $returnArray[$key]->$prop[$yearMonth] += $c->$prop;
                }
            }
        }

        return $returnArray;
    }

    public function checkInitiative($query)
    {
        if ($this->funnelInitiative != '') {
            $inits = [];
            switch ($this->funnelInitiative) {
                case 'At Risk Accepted':
                    $inits = ['At Risk Accepted', 'AtRiskAccepted'];
                    break;
                case 'At Risk App':
                    $inits = ['At Risk App', 'AtRiskApp'];
                    break;
                case 'Attempting FTO':
                    $inits = ['Attempting FTO', 'AttemptingFTO'];
                    break;
                case 'ReEngage AB':
                    $inits = ['ReEngageAB', 'ReEngageAB+DriveAction'];
                    break;
                case 'ReEngage C':
                    $inits = ['ReEngageC', 'ReEngageC+DriveAction'];
                    break;
            }
            $query = $query->whereIn('initiative', $inits);
        }
        return $query;
    }

    public function groupLookup()
    {
        $array = $this->filter->keys();
        $this->keys = $array;
    }

    public function addKeys($query)
    {
        $this->groupLookup();
        $group = $this->group;
        $keys = $this->keys;

        foreach ($query as $q) {
            $code = $q->program;
            $index = (isset($this->keys[$code])) ? $this->keys[$code] : 'Other';
            $query->$group = $index;
            if ($group == 'channel') {
                $q->key = 'Cultivation';
            } elseif ($group == 'initiative') {
                $q->key = 'Conversica';
            } else {
                $q->key = $keys[$code];
            }
        }

        return $query;
    }

    public function createElement($key, $type = '')
    {
        $time = new Time;
        $yearMonths = $time->yearMonths($time->getYear());

        $element = new \StdClass();
        $element->name = $key;

        if ($type == 'leads') {
            $props = ['leads'];
        } elseif ($type == 'deployments') {
            $props = ['deployments'];
        } else {
            $props = $this->metrics;
            array_push($props, 'budget');
        }

        // Create monthly array for each property
        foreach ($props as $prop) {
            $element->$prop = [];
            foreach ($yearMonths as $mo) {
                if (substr($mo, 0, 4) != '2016') {
                    $element->$prop[$mo] = 0;
                }
            }
        }

        return $element;
    }

    public function timeIndex($item)
    {
        $monthString = ($item->month < 10) ? '0' . $item->month : $item->month;
        $yearMonth = $item->year . $monthString;
        return $yearMonth;
    }

    public function starts()
    {
        $programs = $this->filter->programsList();
        $query = ConversicaFullFunnel::whereIn('program', $programs)
            ->where('starts', '>', 0);
        $query = $this->checkInitiative($query);
        
        $query = $query->get(['program', 'year', 'month', 'starts', 'term_year', 'term']);
        return $query;
    }
    public function rangeStarts($rangeArray)
    {
        $starts = $this->starts();
        $array = [];
        foreach ($starts as $s) {
            $yrmo = $this->yearMonth($s->year, $s->month);
            if (in_array($yrmo, $rangeArray)) {
                array_push($array, $s);
            }
        }
        return $array;
    }

    public function yearMonth($year, $month)
    {
        if ((int) $month >= 10) {
            return (string) $year . $month;
        } else {
            return (string) $year . '0' . (string) $month;
        }
    }
}
