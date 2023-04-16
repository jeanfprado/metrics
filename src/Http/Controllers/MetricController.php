<?php

namespace Jeanfprado\Metric\Http\Controllers;

use Illuminate\Routing\Controller;
use Jeanfprado\Metric\Http\Requests\ShowMetric;

class MetricController extends Controller
{
    public function show(ShowMetric $request)
    {
        return response()->json([
            'metric' => $request->metric()->resolve($request),
        ]);
    }
}
