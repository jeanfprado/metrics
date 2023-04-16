<?php

namespace Jeanfprado\Metric\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowMetric extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Get the metric instance for the given request.
     *
     * @return \Jeanfprado\Metrics\Metric
     */
    public function metric()
    {
        $metric = collect(config('metrics.classes'))->first(function ($metric) {
            return $this->metric === (new $metric())->uriKey();
        });

        return new $metric() ?: abort(404);
    }
}
