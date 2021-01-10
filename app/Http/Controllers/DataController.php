<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Setting;

use App\Helpers\Filter;
use App\Helpers\Time;

use App\Helpers\Metrics\Utilities;
use App\Helpers\Metrics\MetricsSum;
use App\Helpers\Metrics\MetricsRange;
use App\Helpers\Metrics\PipelineSum;
use App\Helpers\Metrics\Chart;

use App\Helpers\Metrics\Overview;
use App\Helpers\Metrics\OverviewMonthly;

use App\Helpers\Metrics\Starts;
use App\Helpers\Metrics\Conversion;
use App\Helpers\Metrics\Revenue;

use App\Helpers\Metrics\Profitability;
use App\Helpers\Metrics\ProfitabilityMonthly;

class DataController extends Controller
{
    public function index($type = '')
    {
        return Inertia::render('Admin', 
            [
                'type' => $type,
                'currentUser' => auth()->user()
            ]);
    }

    public function report($type = '')
    {
        return Inertia::render('Reporting', 
            [
                'reportType' => $type,
                'currentUser' => auth()->user()
            ]);
    }

    // Input Status
    public function inputs()
    {
        $array = [];
        $status = Setting::where('key', 'status')->get();
        foreach ($status as $s) {
            $array[$s->type] = $s->value;
        }
        return $array;
    }
    
    // Inertia Pages
    public function metrics($key = '')
    {
        $filter = new Filter;
        $filter->keyword = $key;
        $filter->active = ['TRUE'];
        $filter->group = 'location';
        $filter->keys = [];

        return Inertia::render('Metrics', 
            [
                'requestKey' => $key,
                'requestFilter' => $filter
            ]);
    }

    public function overview($key = '')
    {
        $filter = new Filter;
        $filter->keyword = $key;
        $filter->active = ['TRUE'];
        $filter->group = 'location';
        $filter->keys = [];

        return Inertia::render('Overview', 
            [
                'requestKey' => $key,
                'requestFilter' => $filter
            ]);
    }
    public function termConversion(Request $request)
    {
        $cvr = new Conversion($request);
        return json_encode($cvr->programSemester());
        return 'hello world';
    }


    // Data 
    public function metricTotals()
    {
        $metrics = new MetricsSum;
        $data = $metrics->acquisition();
        return $data;
    }

    public function pipelineTotals(Request $request)
    {
        $pipeline = new PipelineSum();
        $data = $pipeline->funnel();

        return $data;
    }

    // Full Year Chart
    public function chartMetrics()
    {
        $chart = new Chart;
        $query = $chart->query();

        return json_encode($chart);
    }
    // Full Year Chart
    public function chartPipeline(Request $request)
    {
        $chart = new Chart($request);
        $query = $chart->queryPipeline();
        return json_encode($chart);
    }

    public function forecast()
    {
        $metrics = new MetricsRange('forecast');
        $data = $metrics->array();

        return json_encode($data);
    }

    public function overviewData()
    {
        $metrics = new OverviewMonthly;
        return json_encode($metrics->overview());
    }
    
    public function overviewPipeline()
    {
        $metrics = new Overview('pipeline');
        $data = (json_encode($metrics->threeYears()));

        return $data;
    }


    public function revenue()
    {
        $filter = new Filter;

        $filterLastYear = clone $filter;
        $filterLastYear->termYear = $filter->termYear - 1;

        $starts = new Starts;

        $results = $starts->groupResults();

        $array['thisYear'] = $results->where('year', $filter->termYear);
        $array['lastYear'] = $results->where('year', $filter->termYear - 1);

        return json_encode($array);

        // $array['thisYear'] = $this->revenueArray($filter);
        // $array['lastYear'] = $this->revenueArray($filterLastYear);
        // return $array;
    }

    public function revenueSemester(Request $request)
    {
        $revenue = new Revenue($request);
        return $revenue->programSemester($request);
    }

    // Data Updated Dates
    public function dates()
    {
        $time = new Time;
        return $time->dates();
    }

    // Profitability

    // Profitability Report (current & last years)
    public function profitability()
    {
        $profit = new Profitability;
        $data = $profit->profitability();

        return json_encode($data);
    }
    public function profitabilityMonthly()
    {
        $profit = new ProfitabilityMonthly;
        $data = $profit->profitability();

        return json_encode($data);
    }

    public function channelInitiatives($channel = '')
    {
        $util = new Utilities;
        return $util->channelInitiatives($channel);
    }
}
