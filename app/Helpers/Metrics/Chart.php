<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Filter;

use App\Program;

// Performance Metrics
class Chart
{
    public $results;
    public $array;
    
    public $filter;
    public $years;

    // Query Variables
    public $string;
    public $tables;
    public $query;

    public $inputs = [
        'metric',
        'location',
        'bu',
        'program',
        'channel',
        'initiative'
    ];

    public function __construct()
    {
        $this->filter = new Filter;
        
        $this->setYears();
        $this->setTables();
    }

    // Current and prior 2 years
    public function setYears()
    {
        $year = date('Y');
        $years = [];
        for ($i = 0; $i < 3; $i++) {
            $yr = $year - $i;
            array_push($years, $yr);
        }
        $this->years = $years;
    }

    // Tables for each year
    public function setTables()
    {
        $tables = [];
        foreach ($this->years as $yr) {
            $tableName = 'monthly_actuals_' . $yr;
            array_push($tables, $tableName);
        }
        array_push($tables, 'monthly_budget_' . $this->years[0]);
        array_push($tables, 'monthly_forecast_' . $this->years[0]);

        $this->tables = $tables;
    }

    public function queryString()
    {
        $time = new Time;
        $string = 'metric, ';
        if ($this->filter->channel) {
            $string .= 'channel, ';
        }
        $string .= $time->monthSumString();
        return $string;
    }

    public function query()
    {
        $tables = $this->tables;
        $metric = $this->filter->metric;
        $string = $this->queryString();
        $array = [];
        foreach ($tables as $table) {
            $q = DB::table($table)
                ->whereIn('program', $this->filter->programsList())
                ->selectRaw($string);

            if ($this->filter->channel) {
                $q = $q->where('channel', $this->filter->channel);
            }
            if ($this->filter->initiative) {
                $q = $q->where('initiative', $this->filter->initiative);
            }

            
            if ($metric == 'leads' || $metric == 'spend') {
                $q = $q->where('metric', $metric);
            } else {
                $q = $q->whereIn('metric', ['leads', 'spend'])
                    ->orderBy('metric', 'asc')
                    ->groupBy('metric');
            }
            $q = $q->get();
            $array[$table] = $q;
        }
        $this->results = $array;

        $this->parseResults();
        // return $this;
    }

    public function parseResults()
    {
        $time = new Time;
        $results = $this->results;
        $tables = $this->tables;
        $metric = strtolower($this->filter->metric);

        $months = $time->allMonths();
        foreach ($tables as $table) {
            $this->array[$table] = [];
            $data = $results[$table];
            foreach ($months as $month) {
                if ($metric == 'leads' || $metric == 'spend') {
                    $value = $data[0]->$month;
                    array_push($this->array[$table], (float)$value);
                } else {
                    if (count($data) > 0) {
                        $value = ($data[0]->$month > 0) ? $data[1]->$month / $data[0]->$month : 0;
                        array_push($this->array[$table], (float)$value);
                    }
                }
            }
        }
    }


    public function pipelineString()
    {
        $time = new Time;
        $string = 'metric, year, ';
        if ($this->filter->channel) {
            $string .= 'channel, ';
        }
        $string .= $time->monthSumString();
        $this->string = $string;
        return $string;
    }
    // Pipeline Metrics
    public function queryPipeline()
    {
        $years = $this->years;
        $metric = $this->filter->metric;
        $string = $this->pipelineString();
        $array = [];
        foreach ($years as $year) {
            $q = DB::table('monthly_metrics')
                ->where('year', $year)
                ->where('metric', $metric)
                ->whereIn('program', $this->filter->programsList())
                ->selectRaw($string);

            if ($this->filter->channel) {
                $q = $q->where('channel', $this->filter->channel);
            }
            if ($this->filter->initiative) {
                $q = $q->where('initiative', $this->filter->initiative);
            }


            // if ($metric == 'leads' || $metric == 'spend') {
            //     $q = $q->where('metric', $metric);
            // } else {
            //     $q = $q->whereIn('metric', ['leads', 'spend'])
            //         ->orderBy('metric', 'asc')
            //         ->groupBy('metric');
            // }
            $q = $q->get();
            $array[$year] = $q;
        }
        $this->results = $array;

        // $this->parsePipelineResults();
        // return $this;
    }

    public function parsePipelineResults()
    {
        $time = new Time;
        $results = $this->results;
        $tables = ['monthly_metrics'];
        $metric = $this->filter->metric;

        $months = $time->allMonths();
        foreach ($tables as $table) {
            $this->array[$table] = [];
            $data = $results[$table];
            foreach ($months as $month) {
                if ($metric == 'leads' || $metric == 'spend') {
                    $value = $data[0]->$month;
                    array_push($this->array[$table], (float)$value);
                } else {
                    $value = ($data[0]->$month > 0) ? $data[1]->$month / $data[0]->$month : 0;
                    array_push($this->array[$table], (float)$value);
                }
            }
        }
    }

}
