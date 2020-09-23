<?php

namespace App\Helpers\Metrics;

use DB;
use App\Helpers\Time;
use App\Helpers\Filter;
// use App\Helpers\Conversion;

use App\Models\TermConversion;
use App\Models\Program;

// Performance Metrics
class Conversion
{
    public $filter;
    public $group;

    // Construct with Filter
    public function __construct()
    {
        $filter = new Filter;
        $this->filter = $filter;
        $this->group = strtolower($filter->group);
    }

    public function programSemester()
    {
        $columns = ['program', 'year', 'term', 'leads', 'plan_leads', 'starts', 'plan_starts'];
        $metrics = ['leads', 'plan_leads', 'starts', 'plan_starts'];

        $yr = $this->filter->termYear ?? $this->filter->year;
        $sem = $this->filter->semester;
        $terms = $this->semesterTerms($sem);

        $list = $this->filter->programsList();
        $cvr = TermConversion::whereIn('program', $list)
            ->where('year', $yr)
            ->whereIn('term', $terms)
            ->get($columns);

        $returnArray = [];
        foreach ($cvr as $c) {
            $program = $c->program;
            $c->term = strtoupper($c->term);
            $term = $c->term;

            $sum = 0;
            foreach ($metrics as $m) {
                $sum += $c->$m;
            }
            if ($sum > 0) {
                // Array of programs
                if (!array_key_exists($program, $returnArray)) {
                    $returnArray[$program] = new \StdClass();
                }
                // Object of each term with semester total

                if (! property_exists($returnArray[$program], $sem)) {
                    $returnArray[$program]->$sem = new \StdClass();
                    $returnArray[$program]->$sem->leads = 0;
                    $returnArray[$program]->$sem->plan_leads = 0;
                    $returnArray[$program]->$sem->starts = 0;
                    $returnArray[$program]->$sem->plan_starts = 0;
                }

                if (! property_exists($returnArray[$program], $term)) {
                    $returnArray[$program]->$term = new \StdClass();
                    $returnArray[$program]->$term->leads = 0;
                    $returnArray[$program]->$term->plan_leads = 0;
                    $returnArray[$program]->$term->starts = 0;
                    $returnArray[$program]->$term->plan_starts = 0;
                }
                // Add to appropriate semester
                $returnArray[$program]->$sem->leads += $c->leads;
                $returnArray[$program]->$sem->plan_leads += $c->plan_leads;
                $returnArray[$program]->$sem->starts += $c->starts;
                $returnArray[$program]->$sem->plan_starts += $c->plan_starts;

                // Add to appropriate term
                $returnArray[$program]->$term->leads += $c->leads;
                $returnArray[$program]->$term->plan_leads += $c->plan_leads;
                $returnArray[$program]->$term->starts += $c->starts;
                $returnArray[$program]->$term->plan_starts += $c->plan_starts;
            }
        }

        return $returnArray;
    }


    public function semesterTerms($semester)
    {
        switch ($semester) {
            case 'Spring':
            case 'spring':
                return ['A1', 'A2', 'A3', 'a1', 'a2', 'a3'];
                break;
            case 'Summer':
            case 'summer':
                return ['B1', 'B2', 'B3', 'b1', 'b2', 'b3'];
                break;
            case 'Fall':
            case 'fall':
                return ['C1', 'C2', 'C3', 'c1', 'c2', 'c3'];
                break;
            default:
                return [];
                break;
        }
    }
}
