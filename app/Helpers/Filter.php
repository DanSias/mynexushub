<?php

namespace App\Helpers;

use App\Models\Deadline;
use App\Models\Partner;
use App\Models\Program;
use App\Models\ProgramMap;

// neXus Filter:
class Filter
{
    // Define Filter
    public $selected;
    public $selectedGroup;
    public $group;
    public $subgroup;
    public $excludeGroup;
    public $exclude;
    public $excludeChannels;
    public $location;
    public $bu;
    public $partner;
    public $program;
    public $programs;
    public $level;
    public $type;
    public $vertical;
    public $subvertical;
    public $active;
    public $channel;
    public $initiative;
    public $query;
    public $sort;
    public $order;
    public $vintage;
    public $list;
    public $useMine;
    public $termYear;
    public $semester;
    public $term;
    public $budgetType;
    public $strategy;
    public $yearStart;
    public $conversica;
    public $businessCases;

    // Keys for grouping
    public $keys;

    // Single term
    public $keyword;

    /**
     * Build object from Laravel Request Object
     */
    public function __construct()
    {
        $filter = request()->filter;
        if (is_string($filter)) {
            $filter = json_decode($filter);
        }
        if (is_array($filter) || is_object($filter)) {
            foreach ($filter as $key => $value) {
                $this->$key = $value;
            }
        }
        $this->keys = $this->keys();

        // Pull from Request 
        if(request()->partner) {
            $this->partner = [request()->partner];
        }
        
        return $this;
    }

    /**
     * Programs objects associated with the current filter state
     */

    public function programs()
    {
        $query = Program::query();

        $attributes = [
            'active',
            'location', 
            'bu', 
            'vertical', 
            'subvertical',
            'partner',
            'strategy',
            'type',
            'level'
        ];

        foreach ($attributes as $attribute) {
            if ($this->$attribute) {
                $array = $this->checkArray($this->$attribute);
                if ($attribute == 'bu') {
                    if (count($array) == 1) {
                        $array = [$array];
                    }
                }
                if ($attribute == 'partner') {
                    $partnerId = Partner::whereIn('code', $this->partner)->pluck('id')->toArray();
                    $query = $query->whereIn('partner_id', $partnerId);
                } else {
                    $query = $query->whereIn($attribute, $array);
                }
            }
        }

            // Maryville Vintage
            if ($this->vintage != '') {
                switch ($this->vintage) {
                    case 'existing':
                        $query = $query->where('start_year', '<=', 2017);
                        break;
                    case 'y1':
                        $query = $query->where('start_year', 2018);
                        break;
                    case 'y2':
                        $query = $query->where('start_year', 2019);
                        break;
                    case 'y3':
                        $query = $query->where('start_year', 2020);
                        break;
                    default:
                        break;
                }
            }


        // A single item is selected
        if ($this->selected) {
            $subGroup = $this->selectedGroup;
            $selection = $this->selected;
            if ($subGroup == 'School') {
                $subGroup = 'partner';
            }
            if (strtolower($subGroup) == 'program') {
                $query = $query->where('code', $selection);
            } elseif ($subGroup != 'channel') {
                $query = $query->where($subGroup, $selection);
            }
        }

        // List of programs is set 
        if ($this->programs && is_array($this->programs)) {
            $query = $query->whereIn('code', $this->programs);
        }
        // Single program in filter
        if ($this->program && is_string($this->program)) {
            $query = $query->where('code', $this->program);
        }

        // Year Start is selected
        if ($this->yearStart && count($this->yearStart) > 0) {
            $query = $query->whereIn('start_year', $this->yearStart);
        }

        // Exclude
        $excludeGroup = strtolower($this->excludeGroup);
        $exclude = $this->exclude;
        if ($exclude && $excludeGroup == 'program') {
            $query = $query->whereNotIn('code', $exclude);
        } elseif ($exclude && $excludeGroup == 'location') {
            $query = $query->whereNotIn('location', $exclude);
        } elseif ($exclude && $excludeGroup == 'vertical') {
            $query = $query->whereNotIn('vertical', $exclude);
        }

        $query = $query->orderBy('bu', 'asc')
            ->orderBy('code', 'asc');

        $programs = $query->get();

        return $programs;
    }

    // Array of Program Codes
    public function programsList()
    {
        $programs = $this->programs();
        $list = [];
        foreach ($programs as $program) {
            if (!in_array($program->code, $list)) {
                array_push($list, $program->code);
            }
        }
        return $list;
    }

    public function checkArray($payload)
    {
        $array = [];
        if (is_string($payload)) {
            $array = [$payload];
        } else {
            $array = $payload;
        }
        return $array;
    }

    // The key for each item in the group
    public function groupKeyColumns($group = '')
    {
        if ($group == '') {
            $group = strtolower($this->group);
        }
        if ($group == '') {
            $group = strtolower($this->subgroup);
        }
        // Columns to pull from programs table
        $columns = ['code'];

        // Translate group to attribute column
        switch ($group) {
            case 'channel':
            case 'initiative':
                return ['code'];
                break;
            case 'program':
                $group = 'code';
                break;
            case 'business unit':
            case 'bu':
                $group = 'bu';
                break;
            case 'school':
                $group = 'partner';
                break;
            case 'degree type':
                $group = 'type';
                break;
            case 'degree level':
                $group = 'level';
                break;
        }

        array_push($columns, $group);
        
        return $columns;
    }

    public function keys()
    {
        $group = strtolower($this->group);
        if ($group == '' || $this->selected != '') {
            $group = strtolower($this->subgroup);
        }
        if ($group == 'channel' || $group == 'initiative') {
            return [];
        } elseif ($group == 'program') {
            $group = 'code';
        }
        
        $columns = $this->groupKeyColumns();
        $columns = array_filter($columns);

        $programs = $this->programsList();
        
        $query = Program::whereIn('code', $programs)
            ->get($columns);

        $array = [];
        
        foreach ($query as $q) {
            $array[$q->code] = ($q->$group != null) ? $q->$group : 'other';
        }

        return $array;
    }

    public function keysSubgroup()
    {
        $subgroup = strtolower($this->subgroup);
        $columns = $this->groupKeyColumns($subgroup);
        if (count($columns) == 0) {
            return [];
        }

        $programs = $this->programsList();
        
        $query = Program::whereIn('code', $programs)
            ->get($columns);

        $array = [];
        
        foreach ($query as $q) {
            if ($subgroup == 'program') {
                $array[$q->code] = $q->code;
            } else {
                $array[$q->code] = ($q->$subgroup != null) ? $q->$subgroup : 'other';
            }
        }
        return $array;
    }

    // Array of Program Maps for current filtered programs
    public function programMapsList()
    {
        $programs = $this->programsList();
        return ProgramMap::whereIn('code', $programs)
            ->pluck('laurus')
            ->toArray();
    }

    public function deadlineYears()
    {
        $dl = Deadline::whereIn('program', $this->programMapsList())
            ->where('year', $this->termYear);

            if ($this->term) {
                $dl = $dl->where('term', $this->term);
            }

        $dl = $dl->distinct('app')
            ->orderBy('app', 'asc')
            ->pluck('app')
            ->toArray();
        foreach ($dl as $d) {
            // $yrmo =
        }
        return $dl;
    }

    public function keyword($keyword)
    {
        $this->keyword = $keyword;

        return $this->keyword . 'kw';
    }
}
