<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Filter;

use App\Models\Revenue as RevenueTable;
use App\Models\Program;
use App\Models\ProgramCode;

// Performance Metrics
class Revenue
{
    public $filter;
    public $query;
    // Laurus Codes
    public $codes;
    // Based on Group
    public $keys;
    // Laurus -> neXus
    public $map;
    public $metrics = [
        'starts',
        'students',
        'revenue',
        'plan_starts',
        'plan_students',
        'plan_revenue'
    ];

    public function __construct($request = null)
    {
        $this->filter = new Filter;
        $this->allRevenueCodes();
        $this->getMap();
    }

    public function programObject($item)
    {
        $obj = new \StdClass();
        $obj->year = $item->year;
        $group = $this->filter->group;
        $obj->$group = $item->$group;
        $obj->starts = 0;
        return $obj;
    }

    public function groupResults()
    {
        $query = $this->query();

        $group = $this->filter->group;
        $keys = $this->filter->keys();

        foreach ($query as $q) {
            $q->$group = $keys[$q->program];
        }

        // $returnArray = [];

        // if ($group == 'code') {
        //     foreach ($query as $q) {
        //         $year = $q->year;
        //         if (! array_key_exists($returnArray, $year)) {
        //             $returnArray[$year] = [];
        //         }
        //         $key = $q->$group;
        //         if (! array_key_exists($returnArray[$year], $key)) {
        //             $returnArray[$year][$key] = $this->programObject($q);
        //         } else {
        //         }
        //     }
        // }
        // return $returnArray;
        // $results = [];
        // foreach ($query as $q) {
        //     if (! array_key_exists($results, $q->year)) {
        //         $results[$q->year] = [];
        //     }
        //     $group = $this->filter->group;
        //     if (! array_key_exists($results[$q->year], $q->$group)) {
        //         // $prop = $q->$group;
        //         // $results->$prop = new \StdClass();
        //         // array_push($results[$q->year], 'asdf');
        //         $results[$q->year][$q->$group] = [];
        //     }
        // }
        return $query;
    }

    // Query a DB table
    public function query()
    {
        $this->selectTable()
            ->selectPrograms()
            ->selectYear()
            ->selectTerms()
            ->getResults();

        return $this->query;
    }

    // Table, select years and range
    public function selectTable()
    {
        $query = RevenueTable::query();
        
        $this->query = $query;
        return $this;
    }

    // Programs
    public function selectPrograms()
    {
        $query = $this->query;
        $query = $query->whereIn('program', $this->filter->programsList());
        $this->query = $query;
        return $this;        
    }

    // Year
    public function selectYear()
    {
        $query = $this->query;
        $year = $this->filter->termYear;
        if (isset($year)) {
            $query = $query->where('year', '>=', $year - 1);
        } else {
            $firstYear = date('Y') - 2;
            $query = $query->where('year', '>=', $firstYear);
        }
        $this->query = $query;
        return $this;
    }

    // Terms or Semesters
    public function selectTerms()
    {
        $query = $this->query;
        $semester = ($this->filter->semester) ? $this->filter->semester->label : '';
        $term = $this->filter->term;

        if ($semester != '' && $semester != 'All') {
            if ($semester == 'Spring' || $semester == 'Spr') {
                $termArray = ['A1', 'A2', 'A3'];
            } elseif ($semester == 'Summer' || $semester == 'Sum') {
                $termArray = ['B1', 'B2', 'B3'];
            } elseif ($semester == 'Fall') {
                $termArray = ['C1', 'C2', 'C3'];
            }
            $query = $query->whereIn('term', $termArray);
        }
        if ($term != '' && $term != 'All') {
            $query = $query->where('term', $term);
        }
        $this->query = $query;
        return $this;
    }

    public function getResults()
    {
        $columns = [
            'program_rolled',
            'program',
            'year',
            'term',
            'starts',
            'plan_starts',
            'students',
            'plan_students',
            'revenue',
            'plan_revenue'
        ];

        $query = $this->query;
        $query = $query->get($columns);
        $this->query = $query;

        return $this;
    }

    public function modelPrograms()
    {
        $programs = RevenueTable::distinct(['program_rolled'])->get(['program_rolled']);

        $codes = [];
        foreach ($programs as $p) {
            $code = $p->program_rolled;
            array_push($codes, $code);
        }

        $group = $this->filter->group;
        if ($this->filter->group != 'program') {
            $keys = $this->modelProgramKeys($codes);
        }

        $this->keys = $keys;
        return $this;
    }

    public function modelProgramKeys()
    {
        $codes = ProgramCode::whereIn('laurus', $this->codes)->distinct('code')->pluck('code')->toArray();

        $programs = Program::whereIn('code', $codes);
        $programs = $programs->get(['code', $this->filter->group]);

        $this->map = $programs;

        return $this;
    }

    public function getMap()
    {
        $map = ProgramCode::get();
        // $this->map = $map;
        $array = [];
        foreach ($map as $m) {
            $array[$m->laurus] = $m->code;
        }
        $this->map = $array;
    }

    public function allCodesMap()
    {

        foreach ($query as $q) {
            $pro = $q->program_rolled;
            
        }
        $this->query = $query;
    }

    public function allRevenueCodes()
    {
        $codes = RevenueTable::query()->distinct('program_rolled')->pluck('program_rolled')->toArray();
        $this->codes = $codes;
    }

    public function allKeys()
    {
        $codes = $this->codes;
    }

    public function getCode($laurus)
    {
        if (isset($this->map[$laurus])) {
            return $this->map[$laurus];
        } else {
            return 'Other';
        }
    }

    public function getKey($laurus)
    {
        $code = $this->getCode($laurus);

    }

    public function semesterTerms($semester)
    {
        switch ($semester) {
            case 'Spring':
                return ['A1', 'A2', 'A3', 'a1', 'a2', 'a3'];
                break;
            case 'Summer':
                return ['B1', 'B2', 'B3', 'b1', 'b2', 'b3'];
                break;
            case 'Fall':
                return ['C1', 'C2', 'C3', 'c1', 'c2', 'c3'];
                break;
            default:
                return [];
                break;
        }
    }
    

    public function programSemester($request)
    {
        $filter = new Filter;

        $columns = ['program', 'year', 'term', 'starts', 'plan_starts', 'students', 'plan_students', 'revenue', 'plan_revenue'];
        $metrics = ['starts', 'plan_starts', 'students', 'plan_students', 'revenue', 'plan_revenue'];

        $yr = $filter->termYear ?? $filter->year;
        $sem = $filter->semester;
        $terms = $this->semesterTerms($sem);

        $list = $filter->programsList();
        $rev = RevenueTable::whereIn('program', $list)
            ->where('year', $yr)
            ->whereIn('term', $terms)
            ->get($columns);

        $returnArray = [];
        foreach ($rev as $r) {
            $program = $r->program;
            $r->term = strtoupper($r->term);
            $term = $r->term;

            $sum = 0;
            foreach ($metrics as $m) {
                $sum += $r->$m;
            }
            if ($sum > 0) {
                // Array of programs
                if (!array_key_exists($program, $returnArray)) {
                    $returnArray[$program] = new \StdClass();
                }
                // Object of each term with semester total
                if (! property_exists($returnArray[$program], $sem)) {
                    $returnArray[$program]->$sem = new \StdClass();
                    $returnArray[$program]->$sem->starts = 0;
                    $returnArray[$program]->$sem->plan_starts = 0;
                    $returnArray[$program]->$sem->students = 0;
                    $returnArray[$program]->$sem->plan_students = 0;
                    $returnArray[$program]->$sem->revenue = 0;
                    $returnArray[$program]->$sem->plan_revenue = 0;
                }

                if (! property_exists($returnArray[$program], $term)) {
                    $returnArray[$program]->$term = new \StdClass();
                    $returnArray[$program]->$term->starts = 0;
                    $returnArray[$program]->$term->plan_starts = 0;
                    $returnArray[$program]->$term->students = 0;
                    $returnArray[$program]->$term->plan_students = 0;
                    $returnArray[$program]->$term->revenue = 0;
                    $returnArray[$program]->$term->plan_revenue = 0;
                }
                // Add to appropriate semester
                $returnArray[$program]->$sem->starts += $r->starts;
                $returnArray[$program]->$sem->students += $r->students;
                $returnArray[$program]->$sem->revenue += $r->revenue;
                $returnArray[$program]->$sem->plan_starts += $r->plan_starts;
                $returnArray[$program]->$sem->plan_students += $r->plan_students;
                $returnArray[$program]->$sem->plan_revenue += $r->plan_revenue;

                // Add to appropriate term
                $returnArray[$program]->$term->starts += $r->starts;
                $returnArray[$program]->$term->students += $r->students;
                $returnArray[$program]->$term->revenue += $r->revenue;
                $returnArray[$program]->$term->plan_starts += $r->plan_starts;
                $returnArray[$program]->$term->plan_students += $r->plan_students;
                $returnArray[$program]->$term->plan_revenue += $r->plan_revenue;
            }
        }

        return $returnArray;
    }
}
