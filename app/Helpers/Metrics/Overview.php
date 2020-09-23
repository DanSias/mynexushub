<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Range;
use App\Helpers\Filter;

use App\Models\Program;
use App\Helpers\Program\Details;

// Performance Overview (PD Dashboard)
class Overview
{
    public $filter;
    public $range;
    public $time;

    public $channel;
    public $table;
    public $type;
    public $query;
    public $array = [];
    public $index;
    public $metric;
    public $year;
    public $keys;

    // Date Range and Metric Columns

    // Attributes in programs table
    public $attributes = [
        'partner',
        'location',
        'bu',
        'vertical',
        'subvertical',
        'strategy',
        'type',
        'level',
        'strategy'
    ];

    public $pipelineMetrics = [
        'contact',
        'contact15',
        'quality',
        'quality30',
        'app',
        'insales',
        'aip',
        'app30',
        'comfile',
        'comfile60',
        'acc',
        'acc90',
        'accconf',
        'accconf120',
        'start',
        'startsleadmonth',
        'startsleadmonth90',
        'startsleadmonth180'
    ];

    public function __construct($type = '')
    {
        $this->setType($type);

        $this->filter = new Filter;
        $this->range = new Range;
        $this->time = new Time;

        // Overview dashboard always group by program
        $this->filter->group = 'program';

        $year = $this->range->currentYear();
        $this->setYear($year);
        $this->setTable('monthly_actuals_' . $year);
        $this->setKeys();
    }
    public function setArray($array)
    {
        $this->array = $array;
    }
    public function setIndex($index)
    {
        $this->index = $index;
    }
    public function setMetric($metric)
    {
        $this->metric = $metric;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function setTable($table)
    {
        $this->table = $table;
    }
    public function setQuery($query)
    {
        $this->query = $query;
    }
    public function setYear($year)
    {
        $this->year = $year;
    }

    // Current year and previous to match $count
    public function years($count = 3)
    {
        $time = new Time;
        $year = $time->getYear();
        if ($this->type == 'forecast') {
            return [$year];
        }
        $years = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            array_push($years, $year - $i);
        }
        return $years;
    }

    // Latest 3 years for a given type
    // actuals, budget, forecast, pipeline
    public function threeYears()
    {
        $time_start = microtime(true);

        $type = $this->type;
        if (count($this->filter->programsList()) == 1) {
            $years = $this->years(5);
        } else {
            $years = $this->years();
        }

        // Pipeline table has all years, no need to loop
        if ($type == 'pipeline') {
            $query = $this->query();
            $time_query = microtime(true);
            foreach ($query as $q) {
                $this->addQueryRowToArray($q);
            }
        } else {
            foreach ($years as $year) {
                $this->setYear($year);
                $query = $this->query();
                $time_query[$year] = microtime(true);
                foreach ($query as $q) {
                    $this->addQueryRowToArray($q);
                }
            }
        }

        $time_complete = microtime(true);
        $object = new \StdClass();
        $object->$type = $this->array;
        $object->filter = $this->filter;
        $object->time = new \StdClass();
        $object->time->complete = $time_complete - $time_start;
        if (is_array($time_query)) {
            foreach ($time_query as $key => $value) {
                $object->time->query[$key] = $value - $time_start;
            }
        } else {
            $object->time->query = $time_query - $time_start;
        }
        return $object;
    }

    public function addQueryRowToArray($q)
    {
        $metric = strtolower($q->metric);
        if ($metric == 'ï»¿quality') {
            $metric = 'quality';
        }
        $this->metric = $metric;
        $index = $this->checkIndex($q);
        $this->checkArray();

        $year = (isset($q->year)) ? $q->year : $this->year;

        foreach ($this->time->monthDigits as $digits) {
            $yearMonth = $year . $digits;
            $this->array[$index]->$metric->$yearMonth += $q->$digits;
        }
    }

    public function checkArray()
    {
        $array = $this->array;

        $index = $this->index;
        if (!array_key_exists($index, $array)) {
            $array[(string)$index] = new \StdClass();
        }

        $metric = $this->metric;
        // Metric with yearmonth for latest 3 years
        if (!isset($array[$index]->$metric) && ! property_exists($array[$index], $metric)) {
            $array[$index]->$metric = $this->emptyObject();
        }
        $this->setArray($array);
        // return $this;
    }

    // Return the index for each query item
    public function checkIndex($q)
    {
        $index = (isset($this->keys[$q->program])) ? $this->keys[$q->program] : 'Other';

        $this->setIndex($index);
        return $index;
    }

    public function checkArrayForGroup($array, $index)
    {
        if (!array_key_exists($index, $array)) {
            $array[$index] = new \StdClass();
        }
        return $array;
    }

    public function checkArrayForYear($array)
    {
        $year = $this->year;
        if (!isset($array[$year])) {
            $array[$year] = new \StdClass();
        }
        return $array;
    }
    public function checkYearArrayForGroup($array, $group)
    {
        $year = $this->year;
        if (!isset($array[$year]->$group)) {
            $array[$year]->$group = new \StdClass();
        }
        return $array;
    }

    public function checkGroupForMetric($array, $group, $metric)
    {
        $year = $this->year;
        $metric = strtolower($metric);

        if (!isset($array[$year]->$group->$metric)) {
            $array[$year]->$group->$metric = $this->emptyObject();
        }
        return $array;
    }

    // Query a table
    public function query()
    {
        $this->selectTable()
            ->selectString()
            ->selectMetrics()
            ->selectPrograms()
            ->selectChannels()
            ->selectGroups()
            ->selectConversica()
            ->getResults();
        return $this->query;
    }

    public function selectTable()
    {
        $type = $this->type;
        $year = $this->year;

        switch ($type) {
            case 'actuals':
                $table = 'monthly_actuals_';
                $table .= $year;
                break;
            case 'budget':
                $table = 'monthly_budget_';
                $table .= $year;
                break;
            case 'forecast':
                $table = 'monthly_forecast_';
                $table .= $year;
                break;
            case 'metrics':
                $table = 'monthly_metrics';
                break;
            default:
                $table = 'monthly_metrics';
                break;
        }

        // Oritinal budget for 2018
        if ($type == 'budget' && $this->filter->budgetType == 'original') {
            $table = 'monthly_budget_' . $year . '_original';
        }

        $this->setTable($table);
        $query = DB::table($table);
        $this->setQuery($query);
        return $this;
    }

    // Query string based on the table
    public function queryString()
    {
        $table = $this->table;
        $group = $this->filter->group;
        $selectedGroup = strtolower($this->filter->selectedGroup);
        if ($selectedGroup == 'program') {
            $group = 'channel';
        }
        $string = $table . '.metric, ';
        // Year if table is monthly metrics
        if ($table == 'monthly_metrics') {
            $string .= $table . '.year, ';
        }
        $string .= $table . '.program, ';
        
        // Channel & Initiative
        if ($this->filter->channel || $group == 'channel') {
            $string .= $table . '.channel, ';
            if ($this->hasInitiative($table)) {
                $string .= $table . '.initiative, ';
            }
        } elseif ($this->filter->initiative || $group == 'initiative') {
            $string .= $table . '.channel, ';
            if ($this->hasInitiative($table)) {
                $string .= $table . '.initiative, ';
            }
        }
        $time = new Time;
        $sumString = $time->monthSumDigits();

        if ($sumString == '') {
            $string = rtrim($string, ', ');
        } else {
            $string .= $sumString;
        }
        return $string;
    }

    public function selectString()
    {
        $queryString = $this->queryString();
        $query = $this->query->select(DB::raw($queryString));
        $this->setQuery($query);
        return $this;
    }

    // Exclude unnecessary metrics
    public function selectMetrics()
    {
        $ignore = ['CPL', 'CompFileDenied', 'convleads', 'convstarts', 'AppRec'];
        $query = $this->query->whereNotIn('metric', $ignore);
        $this->setQuery($query);
        return $this;
    }

    public function selectPrograms()
    {
        $program = $this->table . '.program';
        $query = $this->query->whereIn($program, $this->filter->programsList());
        $this->setQuery($query);
        return $this;
    }

    public function selectChannels()
    {
        // Remove Test Leaeds
        $query = $this->query->where('channel', '!=', 'Test');
        
        $channel = json_decode($this->filter->channel);
        // No channel -> return
        if ($channel == '' || count($channel) == 0 || $channel == null) {
            return $this;
        }
        if ($channel) {
            if (is_string($channel)) {
                $channel = [$channel];
            }
            $query = $query->whereIn('channel', $channel);
        }

        // Exclude Channels
        $exclude = $this->filter->excludeChannels;
        if (count($exclude) > 0) {
            $query = $query->whereNotIn('channel', $exclude);
        }

        if (count($this->filter->initiative) > 0 && $this->hasInitiative()) {
            if (count($this->filter->initiative) == 1) {
                $query = $query->where('initiative', $this->filter->initiative);
            } else {
                $query = $query->whereIn('initiative', $this->filter->initiative);
            }
        }

        $this->setQuery($query);
        return $this;
    }

    public function selectGroups()
    {
        // Always group by Metric
        $query = $this->query->groupBy('metric');
        $group = 'program';
        $subgroup = $this->filter->subgroup;
        if (property_exists($this->filter, 'expandType') && $this->filter->expandType == 'initiative') {
            $group = 'initiative';
        }
        if ($subgroup == 'program' || $subgroup == 'channel' || $subgroup == 'initiative') {
            $group = $subgroup;
        }
        $table = $this->table;
        // If we are grouping by a known attributes, include
        if ($group == 'program' || $group == 'code') {
            $query = $query->groupBy($table . '.program');
        } elseif ($group == 'channel') {
            $query = $query->groupBy($table . '.channel');
            if ($this->hasInitiative($table)) {
                $query = $query->groupBy($table . '.initiative');
            }
        } elseif ($group == 'initiative' && $this->hasInitiative($table)) {
            $query = $query->groupBy($table . '.initiative');
        } elseif (in_array($group, $this->attributes)) {
            $query = $query->groupBy('program');
        }

        // Metrics table: get all years
        if ($this->table == 'monthly_metrics') {
            if (count($this->filter->programsList()) == 1) {
                $years = $this->years(5);
            } else {
                $years = $this->years();
            }
            $query = $query->whereIn('year', $years);
            $query = $query->groupBy('year');
        }
        
        $this->setQuery($query);
        return $this;
    }

    public function selectConversica()
    {
        $query = $this->query;
        $table = $this->table;
        if ($this->filter->conversica == false) {
            if ($this->hasInitiative($table)) {
                $query = $query->where('initiative', 'not like', '%CONVERSICA%');
            }
        }
        $this->setQuery($query);
        return $this;
    }

    public function getResults()
    {
        $query = $this->query;
        $query = $query->get();
        $this->setQuery($query);
        return $this;
    }

    public function emptyObject()
    {
        // $year = (isset($this->year)) ? $this->year : date('Y');
        $years = $this->years();
        $digits = $this->time->monthDigits;
        $object = new \StdClass();
        foreach ($years as $year) {
            foreach ($digits as $digit) {
                $yrmo = $year . $digit;
                $object->$yrmo = 0;
            }
        }
        return $object;
    }

    // Table with no Initiative Column
    public function hasInitiative($table = '')
    {
        if ($table == '') {
            $table = $this->table;
        }
        if ($table == 'monthly_budget_2016') {
            return false;
        } else {
            return true;
        }
    }

    public function setKeys()
    {
        $array = $this->filter->keys();
        $this->keys = $array;
    }
}
