<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Range;
use App\Helpers\Filter;
use App\Helpers\Metrics\Utilities;

// Performance Metrics
class PipelineSum
{
    public $query;
    public $filter;
    public $range;
    public $time;
    public $table = 'monthly_metrics';

    public $metrics = [
        'contact',
        'contact15',
        'quality',
        'quality30',
        'insales',
        'aip',
        'app30',
        'comfile',
        'comfile60',
        'acc',
        'acc90',
        'accconf',
        'accconf120',
        'startsleadmonth',
        'startsleadmonth90',
        'startsleadmonth180'
    ];

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
    
    public function __construct()
    {
        $this->filter = new Filter;
        $this->range = new Range;
    }

    // Calculate values for each metric 
    public function funnel()
    {
        $array = $this->query();
        $returnArray = $this->buildReturnArray();
        $keys = $this->filter->keys();
            
        // Loop through each year, skipping years if needed
        foreach ($this->range->years() as $year) {
            if (! array_key_exists($type, $array[$year])) {
                continue;
            }

            $items = $array[$year][''];
            foreach ($items as $item) {
                $program = $item->program;
                $metric = strtolower($item->metric);

                $group = strtolower($this->filter->group);
                $subgroup = strtolower($this->filter->subgroup);

                // Get and name object based on group and subgroup
                if ($subgroup == 'channel' || $subgroup == 'initiative') {
                    $key = $item->$subgroup;
                    $returnArray[$key]->name = $item->$subgroup;
                } elseif ($group == 'channel' || $group == 'initiative') {
                    $key = $item->$group;
                    $returnArray[$key]->name = $item->$group;
                } elseif ($group == 'location') {
                    $key = $keys[$program];
                    $returnArray[$key]->name = $keys[$item->program];
                } elseif ($group != 'program' && $group != 'code') {
                    $key = $keys[$program];
                    $returnArray[$key]->name = $keys[$item->program];
                } else {
                    $key = $program;
                    $returnArray[$key]->name = $item->program;
                }

                // if ($group == 'code') {
                //     $key = $program;
                // } elseif ($group == 'channel') {
                //     $key = $item->channel;
                // } elseif ($group == 'initiative') {
                //     $key = $item->initiative;
                // } elseif ($group != 'program') {
                //     $key = $keys[$program];
                // } else {
                //     $key = $program;
                // }
                // if ($key == 'Other Or Unmatched') {
                //     $key = 'OTHER';
                // }
                if (! array_key_exists($key, $returnArray) && $item->total <= 0) {
                    continue;
                }
                // if (!array_key_exists($key, $returnArray)) {
                //     $key = 'OTHER';
                // }
                $returnArray[$key]->$metric += (int) $item->total;
            }
        }

        $returnObject = new \StdClass();
        $returnObject->data = $returnArray;
        $returnObject->pace = $this->range->pace();

        return json_encode($returnObject);
    }

    public function buildReturnArray()
    {
        $returnArray = [];
        // Catch all with 'OTHER' key
        $returnArray['OTHER'] = new \StdClass();
        foreach ($this->metrics as $metric) {
            $returnArray['OTHER']->$metric = 0;
        }
        $keys = $this->filter->keys();

        foreach ($keys as $key) {
            // Ensure object at that key
            if (! array_key_exists($key, $returnArray)) {
                $returnArray[$key] = new \StdClass();
            }

            foreach ($this->range->years() as $year) {
                $returnArray[$key]->name = $key;

                foreach ($this->metrics as $metric) {
                    $returnArray[$key]->$metric = 0;
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
        foreach ($this->range->years() as $year) {
            if (! isset($strings['actuals'][$year])) {
                continue;
            }
            $rangeString = $strings['actuals'][$year];
            // Bypass empty string
            if ($rangeString == 'SUM() AS total') {
                continue;
            }
            $queryString = $this->buildQueryString($this->table, $rangeString);
            $this->setQueryString($queryString, $this->metrics);
            
            $this->setYear($year)
                ->setPrograms($table)
                ->setChannels($table)
                ->setGroups($table)
                ->getResults();
            $array[$year][$type] = $this->query;
        }
        return $array;
    }
    
    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function setQueryString($string, $metrics)
    {
        $query = DB::table($this->table)
            ->select(DB::raw($string))
            ->whereIn('metric', $this->metrics);
        
        $this->setQuery($query);
        return $this;
    }

    // Query string based on the table
    public function buildQueryString($table, $rangeString)
    {
        $string = $table . '.metric, ';
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


    public function setYear($year)
    {
        $query = $this->query->where($this->table . '.year', $year);
        $this->setQuery($query);
        return $this;
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
        if ($this->filter->conversica == false) {
            $query = $query->where('initiative', 'not like', '%CONVERSICA%');
        }

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

        if ($this->filter->expandType == 'initiative') {
            $group = 'initiative';
        }
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
        } elseif (in_array($group, $this->attributes)) {
            $query = $query->groupBy('program');
        } 
        // If subgroup is channel, group by that as well
        if (strtolower($this->filter->subgroup) == 'channel') {
            $query = $query->groupBy($table . '.channel');
        }

        // Metrics table: get all years
        // if ($this->table == 'monthly_metrics') {
        //     $years = $this->range->years();
        //     $query = $query->whereIn('year', $years);
        //     $query = $query->groupBy('year');
        // }
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

}
