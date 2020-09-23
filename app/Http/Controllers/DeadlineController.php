<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Helpers\Filter;
use App\Models\Program;
use App\Models\Revenue;
use App\Models\TermConversion;
use App\Models\Deadline;

class DeadlineController extends Controller
{
    public $filter;

    public function __construct()
    {
        $this->filter = new Filter;
    }

    // All Deadlines in last 3 years
    public function index()
    {
        $dlQuery = Deadline::where('year', '>=', date('Y') - 2)
            ->whereIn('program', $this->filter->programsList())
            ->whereNotNull('app')
            ->orderBy('program', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc');

        if ($this->filter->termYear) {
            $dlQuery = $dlQuery->where('year', $this->filter->termYear);
        } elseif ($this->filter->year) {
            $dlQuery = $dlQuery->where('year', $this->filter->year);
        }

        if ($this->filter->semester) {
            $dlQuery = $dlQuery->whereIn('term', $this->semesterTerms($this->filter->semester));
        }

        $dlQuery = $dlQuery->get(['program', 'year', 'term', 'app', 'start']);

        $deadlines = $this->format($dlQuery);

        $returnObject = new \StdClass();
        $returnObject->next = $this->getNext($deadlines);
        $returnObject->deadlines = $deadlines;
        $returnObject->range = $this->range($deadlines);

        return json_encode($returnObject);
    }
     
    // Get the closest upcoming deadline from array
    public function getNext($deadlines)
    {
        $next = $deadlines->where('past', false)
            ->sortBy('days')
            ->first();

        // If no future deadlines, most recent past
        if (is_null($next)) {
            $next = $deadlines->where('past', true)
                ->sortBy('days')
                ->first();
        }

        return $next;
    }

    public function next()
    {
        $dl = Deadline::whereIn('program', $this->filter->programsList())
            ->whereNotNull('app')
            ->orderBy(DB::raw('ABS(DATEDIFF(app, NOW()))'))
            ->first();
        $dl->semester = $this->semester($dl->term);
        return $dl;
    }
    
    // YearMonth date range for deadlines
    public function range($deadlines)
    {
        $range['start'] = 1000000;
        $range['end'] = 0;

        foreach ($deadlines as $dl) {
            if ($dl->close > $range['end']) {
                $range['end'] = $dl->close;
            }
            if ($dl->open < $range['start']) {
                $range['start'] = $dl->open;
            }
        }
        
        return $range;
    }


    // Deadlines for a Single Program
    public function program($program)
    {
        return Deadline::where('program', $program)
            ->whereNotNull('app')
            ->groupBy('year')
            ->groupBy('term')
            ->orderBy('year', 'desc')
            ->orderBy('term', 'asc')
            ->get();
    }

    // Get semester from term
    public function semester($term)
    {
        $term = strtoupper($term);
        if (strpos($term, 'A') !== false) {
            return 'Spring';
        } elseif (strpos($term, 'B') !== false) {
            return 'Summer';
        } elseif (strpos($term, 'C') !== false) {
            return 'Fall';
        } else {
            return '';
        }
    }
    // Get array terms for a given semester
    public function semesterTerms($semester)
    {
        $semester = strtolower($semester);
        switch ($semester) {
            case 'spring':
                return ['A1', 'A2', 'A3'];
                break;
            case 'summer':
                return ['B1', 'B2', 'B3'];
                break;
            case 'fall':
                return ['C1', 'C2', 'C3'];
                break;
            default:
                return ['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'C1', 'C2', 'C3'];
        }
    }

    
    // Format deadlines by adding semester, open, and close
    public function format($deadlines)
    {
        foreach ($deadlines as $d) {
            $d->term = strtoupper($d->term);
            if (strpos($d->term, 'A') !== false) {
                $d->semester = 'Spring';
            } elseif (strpos($d->term, 'B') !== false) {
                $d->semester = 'Summer';
            } elseif (strpos($d->term, 'C') !== false) {
                $d->semester = 'Fall';
            } else {
                $d->semester = '';
            }
            // Date Details
            $carbon = ($d->app) ? new Carbon($d->app) : '';
            $d->when = ($d->app) ? $carbon->diffForHumans() : '';
            if ($carbon !== '' && $carbon->isPast()) {
                $d->past = true;
            } else {
                $d->past = false;
            }
            $d->text = ($d->app) ? $carbon->format('F j, Y') : '';
            $d->days = ($d->app) ? $carbon->diffInDays() : '';

            $d->program = (isset($mapArray[$d->program])) ? $mapArray[$d->program] : $d->program;
            $d->code = $d->program;
            $d->programYearTerm = $d->program . $d->year . $d->term;
            if ($d->app) {
                $explode = explode('-', $d->app);
                $yr = (int)$explode[0];
                $mo = (int)$explode[1];
                $day = (int)$explode[2];

                // Last Complete Month
                $lm = $mo - 1;
                // On or after the 15th, include deadline month
                if ($day >= 15) {
                    $lm = $mo;
                }

                if ($lm == 0) {
                    $yr = $yr--;
                    $lm = 12;
                }
                if ($lm < 10) {
                    $lm = 0 . $lm;
                }
                $d->close = $yr . $lm;
                if ($lm == 12) {
                    $d->open = $yr . '01';
                    $d->open = (int) $d->open;
                } else {
                    $d->open = (int) $d->close - 99;
                }
                $d->close = (int) $d->close;
            }
        }
        
        $deadlines = $deadlines->unique('programYearTerm');

        // Deadlines with Data
        $dataTerms = $this->dataTerms();
        foreach ($deadlines as $key => $value) {
            if (! in_array($value->programYearTerm, $dataTerms)) {
                unset($deadlines[$key]);
            }
        }

        return $deadlines;
    }

    // Terms with data in term conversion and revenue tables
    public function dataTerms()
    {
        $revTerms = $this->revenueTerms();
        $convTerms = $this->conversionTerms();

        $array = [];

        foreach ($revTerms as $term) {
            $pyt = $term->program . $term->year . strtoupper($term->term);
            array_push($array, $pyt);
        }
        foreach ($convTerms as $term) {
            $pyt = $term->program . $term->year . strtoupper($term->term);
            if (! in_array($pyt, $array)) {
                array_push($array, $term->program . $term->year . strtoupper($term->term));
            }
        }

        sort($array);

        return $array;
    }

    public function revenueTerms()
    {
        $revTerms = Revenue::where('year', $this->filter->year)
            ->whereNotNull('program')
            ->select(DB::raw('program, year, term, starts + plan_starts as starts'))
            ->whereIn('program', $this->filter->programsList())
            ->whereIn('term', $this->semesterTerms($this->filter->semester))
            ->where('starts', '>', 0)
            ->orWhere('plan_starts', '>', 0)
            ->groupBy('program')
            ->groupBy('year')
            ->groupBy('term')
            ->orderBy('program', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc')
            ->get();
        return $revTerms;
    }

    public function conversionTerms()
    {
        $conversionTerms = TermConversion::where('year', $this->filter->year)
            ->whereNotNull('program')
            ->select(DB::raw('program, year, term, starts + plan_starts as starts'))
            ->whereIn('program', $this->filter->programsList())
            ->whereIn('term', $this->semesterTerms($this->filter->semester))
            ->where('starts', '>', 0)
            ->orWhere('plan_starts', '>', 0)
            ->groupBy('program')
            ->groupBy('year')
            ->groupBy('term')
            ->orderBy('program', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc')
            ->get();
        return $conversionTerms;
    }
}
