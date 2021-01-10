<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Range;
use App\Helpers\Filter;
use App\Helpers\Time;
use App\Helpers\Metrics\Utilities;

// use App\CVRS;
// use App\Helpers\Program\Details;

// Performance Metrics
class MetricsRange
{
    // 
    public $type;
    public $table;
    public $array = [];

    public $filter;
    public $range;
    public $query;

    public function __construct($type = 'actuals')
    {
        $this->type = $type;
        $this->filter = new Filter;
        $this->range = new Range;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    // actuals, budget, forecast, pipeline
    public function array()
    {
        foreach ($this->range->years() as $year) {
            $query = $this->query($year);
            foreach ($query as $q) {
                $this->appendToArray($q);
            }
        }
        

        $object = new \StdClass();
        $type = $this->type;
        $object->$type = $this->array;
        $object->filter = $this->filter;
        
        return $object;
    }


    public function appendToArray($q)
    {
        $metric = strtolower($q->metric);
        if ($metric == 'ï»¿quality') {
            $metric = 'quality';
        }
        $keys = $this->filter->keys;
        if ($this->filter->group == 'channel') {
            $index = $q->channel;
        } else {
            $index = $keys[$q->program];
        }
        if (! array_key_exists($index, $this->array)) {
            $this->array[$index] = new \StdClass();
        }

        $time = new Time;
        $year = (isset($q->year)) ? $q->year : $time->getYear();
        foreach ($time->monthDigits as $digits) {
            $yearMonth = $year . $digits;

            if (! property_exists($this->array[$index], $metric)) {
                $this->array[$index]->$metric = new \StdClass();
            }
            if (property_exists($q, $digits)) {
                if (! property_exists($this->array[$index]->$metric, $yearMonth)) {
                    $this->array[$index]->$metric->$yearMonth = 0;
                }
                $this->array[$index]->$metric->$yearMonth += $q->$digits;
            }
        }
    }


    // Query a table
    public function query($year)
    {
        $this->selectTable($year)
            ->selectString($year)
            ->selectMetrics()
            ->selectPrograms()
            ->selectChannels()
            ->selectGroups()
            ->selectConversica()
            ->getResults();

        return $this->query;
    }

    public function selectTable($year = 0)
    {
        $table = ($year == 0) ? 'monthly_metrics' : 'monthly_' . $this->type . '_' . $year;
        $this->table = $table;
        // Build Query starting with table
        $query = DB::table($table);
        $this->setQuery($query);
        return $this;
    }

    public function selectString($year)
    {
        $queryString = $this->range->sumStrings();
        $string = $this->table . '.metric, ';
        $string .= $this->table . '.program, ';
        if (strtolower($this->filter->subgroup) == 'channel') {
            $string .= $this->table . '.channel, ';
        }
        $string .= $queryString[$year];

        $query = $this->query->select(DB::raw($string));
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
        $query = $this->query->where('channel', '!=', 'Test');
        $channel = $this->filter->channel;
        $selected = $this->filter->selected;
        $exclude = $this->filter->excludeChannels;

        // No channel -> return
        if ($channel == '' || $channel == [] || $channel == null) {
            return $this;
        }
        if ($channel) {
            if (is_string($channel)) {
                $channel = [$channel];
            }
            $query = $query->whereIn('channel', $channel);
        } 

        if (count($exclude) > 0) {
            $query = $query->whereNotIn('channel', $exclude);
        }

        if (count($this->filter->initiative) > 0) {
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
        $group = $this->filter->group;
        $subgroup = strtolower($this->filter->subgroup);
        $selectedGroup = strtolower($this->filter->selectedGroup);
        if ($selectedGroup == 'program') {
            $group = 'channel';
        }
        if ($group == '') {
            $group = $subgroup;
        }

        if ($this->filter->expandType == 'initiative') {
            $group = 'initiative';
        }
        if ($subgroup == 'bu' || $subgroup == 'program' || $subgroup == 'channel' || $subgroup == 'initiative') {
            $group = $subgroup;
            $this->filter->group = $subgroup;
        }
        $table = $this->table;
        // If we are grouping by a known attributes, include
        if ($group == 'program' || $group == 'code') {
            $query = $query->groupBy($table . '.program');
        } elseif ($group == 'channel') {
            $query = $query->groupBy($table . '.channel');
            $query = $query->groupBy($table . '.initiative');
        } elseif ($group == 'initiative') {
            $query = $query->groupBy($table . '.initiative');
        } else {
            $query = $query->groupBy('program');
        } 
        // If subgroup is channel, group by that as well
        if ($subgroup == 'channel' && $group != 'channel') {
            $query = $query->groupBy($table . '.channel');
        }
        // Metrics table: get all years
        if ($this->table == 'monthly_metrics') {
            $years = $this->years();
            $query = $query->whereIn('year', $years);
            $query = $query->groupBy('year');
        }
        $this->setQuery($query);
        return $this;
    }

    public function selectConversica()
    {
        $table = $this->table;
        $query = $this->query;
        if ($this->filter->conversica == false) {
            $query = $query->where('initiative', 'not like', '%CONVERSICA%');
        }
        $this->setQuery($query);
        return $this;
    }

    public function getResults()
    {
        $query = $this->query->get();
        $this->setQuery($query);
        return $this;
    }

}
