<?php

namespace App\Classes\Metrics;

use DB;
use App\Classes\Time;
use App\Classes\Range;
use App\Classes\Filter;

use App\Classes\Metrics\Utilities;

// Lead Tracker
class Tracker
{
    public $filter;
    public $range;
    public $time;

    public $query;
    public $table;

    public function __construct()
    {
        $this->filter = new Filter;
        $this->range = new Range;
        $this->time = new Time;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }
    

    // Calculate values for each metric 
    public function dataObject()
    {
        $array = $this->query();
        $keys = $this->filter->keys;
        $keysSubgroup = $this->filter->keysSubgroup();
        
        $subgroup = strtolower($this->filter->subgroup);
        if ($subgroup == 'channel') {
            $details = new Utilities;
            $allSubgroups = $details->allChannels();
        } else {
            $allSubgroups = array_values($keysSubgroup);
            $allSubgroups = array_unique($allSubgroups);
        }
    
        $group = $this->filter->group;
        if ($group == 'channel') {
            $details = new Utilities;
            $programs = $details->allChannels();
        } elseif ($group == 'initiative') {
            $details = new Utilities;
            $programs = $details->channelInitiatives($this->filter->channel);
        }

        // Build Return Array to structure data
        $returnArray = $this->buildReturnArray();

        $tables = $this->range->tables();
        $years = $this->range->years();
        foreach ($years as $year) {
            foreach ($tables as $type => $metrics) {
                $items = $array[$year][$type];
                foreach ($items as $item) {
                    $pro = $item->program;
                    $metric = strtolower($item->metric);
                    if ($type == 'budget') {
                        $metric = $metric . 'Budget';
                    } elseif ($type == 'forecast') {
                        // $metric = $metric . 'Forecast';
                    }
                    if ($group != 'program') {
                        $key = (string) $keys[$pro];
                    } else {
                        $key = $pro;
                    }
                    // Define subkey
                    if ($subgroup == 'channel' || $subgroup == 'initiative') {
                        $subkey = (string)$item->$subgroup;
                    } elseif ($subgroup == 'program') {
                        $subkey = $pro;
                    } else {
                        $subkey = (string)$keysSubgroup[$pro];
                    }
                    if ($subkey != '99') {
                        $returnArray[$key][$subkey]->$metric += (int) $item->total;
                    }
                }
            }
        }
        // Remove empty subgroup objects
        foreach ($returnArray as $group => $subgroups) {
            foreach ($subgroups as $subgroup => $obj) {
                $sum = $obj->leads + $obj->leadsBudget + $obj->leadsForecast;
                $sum += $obj->spend + $obj->spendBudget + $obj->spendForecast;
                if ($sum == 0) {
                    unset($returnArray[$group][$subgroup]);
                }
            }
        }

        $returnObject = new \StdClass();
        $returnObject->data = $returnArray;
        $returnObject->pace = $this->range->pace();

        return json_encode($returnObject);
    }

    public function query()
    {
        $years = $this->range->years();
        $strings = $this->range->totalStrings();
        $tables = $this->range->tables();
        $array = [];
        foreach ($years as $year) {
            foreach ($tables as $type => $metrics) {
                $this->selectTable($type, $year);
                $string = $strings[$type][$year];
                $queryString = $this->tableQueryString($string);
                $query = $this->query->select(DB::raw($queryString))
                    ->whereIn('metric', $metrics);
                
                $this->setQuery($query);

                $this->selectPrograms()
                    ->selectChannels()
                    ->selectSubgroups()
                    ->selectConversica()
                    ->getResults();
                $array[$year][$type] = $this->query;
            }
        }

        return $array;
    }


    // Query string based on the table
    public function tableQueryString($monthString = '')
    {
        $table = $this->table;

        $string = $table . '.metric, ';
        // Year if table NOT budget
        if (strpos($table, 'budget') === false) {
            $string .= $table . '.year, ';
        }

        $string .= $table . '.program, ';
        
        // Channel & Initiative
        if ($this->filter->channel || $this->filter->group == 'channel') {
            $string .= $table . '.channel, ';
            if ($this->hasInitiative($table)) {
                $string .= $table . '.initiative, ';
            }

        } elseif ($this->filter->initiative || $this->filter->group == 'initiative') {
            $string .= $table . '.channel, ';
            if ($this->hasInitiative($table)) {
                $string .= $table . '.initiative, ';
            }
        }

        $subgroup = strtolower($this->filter->subgroup);
        if ($subgroup == 'channel' || $subgroup == 'initiative') {
            $string .= $table . '.' . $subgroup . ', ';
        }

        if ($monthString == '') {
            $sumString = $this->time->monthSumDigits();
        } else {
            $sumString = $monthString;
        }

        if ($sumString == '') {
            $string = rtrim($string, ', ');
        } else {
            $string .= $sumString;
        }
        return $string;
    }    


    public function selectTable($type, $year)
    {
        $table = 'monthly_' . $type . '_' . $year;

        // Oritinal budget for 2018
        if ($type == 'budget' && $this->filter->budgetType == 'original') {
            $table = 'monthly_budget_' . $year . '_original';
        }

        $query = DB::table($table);
        $this->setQuery($query);
        $this->table = $table;
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

    public function hasInitiative()
    {
        return true;
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


    public function selectSubgroups()
    {
        // Always group by Metric
        $query = $this->query->groupBy('metric');
        
        $group = $this->filter->group;
        $subgroup = strtolower($this->filter->subgroup);
        if ($this->filter->expandType == 'initiative') {
            $group = 'initiative';
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
        } else {
            $query = $query->groupBy('program');
        } 

        // Group by Subgroup as well
        if ($subgroup == 'channel' || $subgroup == 'initiative') {
            $query = $query->groupBy($subgroup);
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


    // Return array for group / subgroup
    public function buildReturnArray()
    {
        $returnArray = [];

        $keys = $this->keys;
        $subkeys = $this->subkeys;
        $group = $this->filter->group;
        $subgroup = $this->filter->subgroup;
        // List of keys
        $keyList = array_values($keys);

        if ($group == 'channel') {
            $details = new Utilities;
            $channels = $details->allChannels();
            $keyList = $channels->toArray();
        } elseif ($group == 'initiative') {
            $details = new Utilities;
            $initiatives = $details->allInitiatives();
            $keyList = $initiatives->toArray();
        }
        // Unique Values
        $keyList = array_unique($keyList);
        $keyList = array_values($keyList);
        sort($keyList);

        // Start with Group
        foreach ($keyList as $key) {
            $key = (string) $key;
            $returnArray[$key] = [];
        }

        // Subgroup
        $subkeyList = array_values($subkeys);

        if ($subgroup == 'channel') {
            $details = new Utilities;
            $channels = $details->allChannels();
            $subkeyList = $channels->toArray();
        } elseif ($subgroup == 'initiative') {
            $details = new Utilities;
            $initiatives = $details->allInitiatives();
            $subkeyList = $initiatives->toArray();
        }
        // Unique Values
        $subkeyList = array_unique($subkeyList);
        $subkeyList = array_values($subkeyList);
        sort($subkeyList);

        // $tables = $this->range->tables();

        // Continue with subkey
        foreach ($returnArray as $key => $value) {
            foreach ($subkeyList as $subkey) {
                $subkey = (string) $subkey;
                $returnArray[$key][$subkey] = new \StdClass();
                $returnArray[$key][$subkey]->group = (string) $key;
                $returnArray[$key][$subkey]->subgroup = (string) $subkey;

                $bases = ['leads', 'spend'];
                $ranges = ['', 'Budget', 'Forecast',];
                foreach ($bases as $base) {
                    foreach ($ranges as $range) {
                        $metric = $base . $range;
                        $returnArray[$key][$subkey]->$metric = 0;
                    }
                }
            }
        }
        return $returnArray;
    }


    public function trackerData()
    {
        // Date Ranges
        $currentRange = [$this->time->actualsYearMonth()->current];
        $completeRange = $this->time->actualsCompleteYearMonths();
        $fullYearRange = $this->time->fullYearMonths();

        // Clone Objects for current month, complete months, and full year
        $currentObject = new \StdClass();
        $currentObject->filter = $this->filter;
        $currentObject->range = $currentRange;

        $completeObject = new \StdClass();
        $completeObject->filter = $this->filter;
        $completeObject->range = $completeRange;

        $fullObject = new \StdClass();
        $fullObject->filter = $this->filter;
        $fullObject->range = $fullYearRange;
        
        // Build data
        $this->range->setRange($currentRange);
        $currentData = $this->dataObject($currentObject);

        $this->range->setRange($completeRange);
        $completeData = $this->dataObject($completeObject);

        $this->range->setRange($fullYearRange);
        $fullData = $this->dataObject($fullObject);

        $returnObject = new \StdClass();
        $returnObject->current = json_decode($currentData);
        $returnObject->complete = json_decode($completeData);
        $returnObject->full = json_decode($fullData);

        return $returnObject;
    }
}