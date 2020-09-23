<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Range;
use App\Helpers\Filter;
use App\Helpers\Metrics\Utilities;

// Performance Metrics
class MetricsSum
{
    public $query;
    public $filter;
    public $range;
    public $time;

    public $metricList = [
        'leads',
        'leadsBudget',
        'leadsForecast',
        'spend',
        'spendBudget',
        'spendForecast',
        'cpl',
        'cplBudget',
        'cplForecast',
    ];
    
    public function __construct()
    {
        $this->filter = new Filter;
        $this->range = new Range;
    }

    // Calculate values for each metric 
    public function acquisition()
    {
        $array = $this->query();
        $returnArray = $this->buildReturnArray();
        
        $tables = $this->range->tables();
        $keys = $this->filter->keys();
        $years = $this->range->years();

        foreach ($years as $year) {
            foreach ($tables as $type => $metrics) {
                $items = $array[$year][$type];
                foreach ($items as $item) {
                    $program = $item->program;
                    $metric = strtolower($item->metric);
                    if ($type == 'budget' || $type == 'forecast') {
                        $metric = $metric . ucfirst($type);
                    }

                    $group = strtolower($this->filter->group);
                    $subgroup = strtolower($this->filter->subgroup);

                    // Get and name object based on group and subgroup
                    if ($subgroup == 'channel' || $subgroup == 'initiative') {
                       $key = $item->$subgroup;
                       $returnArray[$key]->name = $item->$subgroup;
                    } elseif ($group == 'channel' || $group == 'initiative') {
                       $key = $item->$group;
                       $returnArray[$key]->name = $item->$group;
                    } else if ($group == 'location') {
                        $key = $keys[$program];
                        $returnArray[$key]->name = $keys[$item->program];
                    } elseif ($group != 'program' && $group != 'code') {
                        $key = $keys[$program];
                        $returnArray[$key]->name = $keys[$item->program];
                    } else {
                        $key = $program;
                        $returnArray[$key]->name = $item->program;
                    }
                    $returnArray[$key]->$metric += (int) $item->total;
                }
            }
        }

        // Add Forecasts and Actuals together
        foreach ($returnArray as $item => $object) {
            if (property_exists($object, 'leads')) {
                $object->leadsActuals = $object->leads;
                if (property_exists($object, 'leadsForecast')) {
                    $object->leads += $object->leadsForecast;
                }
            }
            if (property_exists($object, 'spend')) {
                $object->spendActuals = $object->spend;
                if (property_exists($object, 'spendForecast')) {
                    $object->spend += $object->spendForecast;
                }
            }
            $object->cpl = ($object->leads > 0) ? $object->spend / $object->leads : 0;
            $object->cplBudget = ($object->leadsBudget > 0) ? $object->spendBudget / $object->leadsBudget : 0;
        }

        $returnArray = $this->removeZeros($returnArray);
        $returnObject = new \StdClass();
        $returnObject->data = $returnArray;
        $returnObject->pace = $this->range->pace();
        $returnObject->filter = $this->filter;

        return json_encode($returnObject);
    }

    public function buildReturnArray()
    {
        $returnArray = [];
        $years = $this->range->years();
        $tables = $this->range->tables();
        $keys = $this->filter->keys();

        foreach ($keys as $key) {
            // Ensure object at that key
            if (! array_key_exists($key, $returnArray)) {
                $returnArray[$key] = new \StdClass();
            }

            foreach ($years as $year) {
                foreach ($tables as $type => $metrics) {
                    $returnArray[$key]->name = $key;

                    foreach ($this->metricList as $metric) {
                        $returnArray[$key]->$metric = 0;
                    }
                }
            }
        }

        return $returnArray;
    }

    // Query table for given range 
    // Return single total for each group item
    public function query()
    {
        // Array of strings by table type and year
        $strings = $this->range->totalStrings();
        $array = [];
        // Loop through all years in the range and all tables
        foreach ($this->range->years() as $year) {
            foreach ($this->range->tables() as $type => $metrics) {

                // If there is no string for this table type, skip
                if (! isset($strings[$type][$year]) || $strings[$type] == []) {
                    continue;
                }
                $rangeString = $strings[$type][$year];

                // Bypass empty string
                if ($rangeString == 'SUM() AS total') {
                    continue;
                }
                $table = $this->setTable($type, $year);
                $queryString = $this->buildQueryString($table, $rangeString);

                $this->setQueryString($queryString, $metrics);

                $this->setPrograms($table)
                    ->setChannels($table)
                    ->setGroups($table)
                    ->getResults();
                $array[$year][$type] = $this->query;
            }
        }
        return $array;
    }
    
    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function setTable($type, $year)
    {
        $hasTable = ['actuals', 'budget', 'forecast'];
        $table = (in_array($type, $hasTable)) ? 'monthly_' . $type . '_' . $year : 'monthly_metrics';
        // Build Query starting with table
        $query = DB::table($table);
        $this->setQuery($query);
        return $table;
    }

    public function setQueryString($string, $metrics)
    {
        $query = $this->query->select(DB::raw($string))
            ->whereIn('metric', $metrics);
        
        $this->setQuery($query);
        return $this;
    }

    // Query string based on the table
    public function buildQueryString($table, $rangeString)
    {
        $string = $table . '.metric, ';
        // Year if table NOT budget
        if (strpos($table, 'budget') === false) {
            $string .= $table . '.year, ';
        }

        $string .= $table . '.program, ';
        
        // Channel & Initiative
        if ($this->filter->channel || $this->filter->group == 'channel') {
            $string .= $table . '.channel, ';
            $string .= $table . '.initiative, ';

        } elseif ($this->filter->initiative || $this->filter->group == 'initiative') {
            $string .= $table . '.channel, ';
            $string .= $table . '.initiative, ';
        }

        $subgroup = strtolower($this->filter->subgroup);
        if ($subgroup == 'channel' || $subgroup == 'initiative') {
            $string .= $table . '.' . $subgroup . ', ';
        }

        if ($rangeString == '') {
            $string = rtrim($string, ', ');
        } else {
            $string .= $rangeString;
        }
        return $string;
    }


    public function setPrograms($table)
    {
        $query = $this->query->whereIn($table . '.program', $this->filter->programsList());
        $this->setQuery($query);
        return $this;
    }

    public function setChannels($table)
    {
        // No test data
        $query = $this->query->where($table . '.channel', '!=', 'Test');
        $channel = $this->filter->channel;
        $selected = $this->filter->selected;
        $exclude = $this->filter->excludeChannels;

        // Conversica
        if ($this->filter->conversica === false) {
            $query = $query->where('initiative', 'not like', '%CONVERSICA%');
        }

        // No channel -> return
        if ($channel == '' || $channel == [] || $channel == '[]' || $channel == null) {
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


    public function setGroups($table)
    {
        // Always group by Metric
        $query = $this->query->groupBy('metric');
        $group = strtolower($this->filter->group);
        $subgroup = $this->filter->subgroup;
        
        $selectedGroup = strtolower($this->filter->selectedGroup);
        if ($selectedGroup == 'program') {
            $group = 'channel';
        }
        if ($group == '') {
            $group = $subgroup;
        }

        // if ($this->filter->expandType == 'initiative') {
        //     $group = 'initiative';
        // }
        if ($subgroup == 'bu' || $subgroup == 'program' || $subgroup == 'channel' || $subgroup == 'initiative') {
            $group = $subgroup;
        }
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
        if (strtolower($this->filter->subgroup) == 'channel') {
            $query = $query->groupBy($table . '.channel');
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
    
    public function removeZeros($array)
    {
        if (strtolower($this->filter->subgroup) == 'channel') {
            foreach ($array as $channel => $metrics) {
                $sum = 0;
                foreach ($metrics as $metric => $value) {
                    $sum += (int) $value;
                }
                if ($sum == 0) {
                    unset($array[$channel]);
                }
            }
        }
        return $array;
    }
}
