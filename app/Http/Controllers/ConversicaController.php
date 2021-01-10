<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Helpers\Metrics\Conversica;
use App\Helpers\Metrics\ConversicaFunnel;
use App\Helpers\Filter;

class ConversicaController extends Controller
{
    public function selects()
    {
        $conversica = new Conversica;
        return $conversica->selects();
    }

    // Combine Actuals, Budgets, and Deployments
    public function data(Request $request)
    {
       $filter = new Filter;
        $conversica = new Conversica($filter);

        $data = new \StdClass();
        $data->actuals = $conversica->actuals();
        $data->targets = $conversica->targets();
        $data->deployments = $conversica->deployments();
        $data->date = $conversica->getDate();
        return json_encode($data);
    }

    public function starts(Request $request)
    {
       $filter = new Filter;
        $conversica = new Conversica($filter);
        $filterStarts = $conversica->starts();

        $range = $request->range;
        $rangeStarts = $conversica->rangeStarts($range);

        return json_encode($rangeStarts);
    }

    public function actuals(Request $request)
    {
       $filter = new Filter;
        $conversica = new Conversica($filter);

        $data = new \StdClass();
        $data->actuals = $conversica->leads();
        return json_encode($data);
    }

    public function merge(Request $request)
    {
        $filter = new Filter;
        $filter->group = 'program';
        
        $conversica = new Conversica($filter);

        $rows = $conversica->merge();
        return view('merge.conversica', compact('rows'));
    }
    public function mergeYear($year)
    {
        $conversica = new Conversica;
        $rows = $conversica->mergeYear($year);
        return view('merge.conversica', compact('rows', 'year'));
    }

    public function mergeTargets()
    {
        $conversica = new Conversica;
        return $conversica->mergeTargets();
    }

    public function funnel(Request $request)
    {
       $filter = new Filter;
        $filter->funnelInitiative = $request->initiative;

        $conversicaFunnel = new ConversicaFunnel($filter);

        $data = new \StdClass();
        $data->actuals = $conversicaFunnel->actuals();
        $deployments = $conversicaFunnel->count();
        foreach ($deployments as $program => $obj) {
            $data->actuals[$program]->deployments = $obj->deployments;
        }

        return json_encode($data);
    }

    public function funnelStarts(Request $request)
    {
       $filter = new Filter;
        $conversica = new ConversicaFunnel($filter);

        $range = $request->range;
        $rangeStarts = $conversica->rangeStarts($range);

        return json_encode($rangeStarts);
    }

}
