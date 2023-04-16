<?php

namespace Jeanfprado\Metric\Features;

use Jeanfprado\Metric\Metric;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Value extends Metric
{
    /**
     * The value's precision when rounding.
     *
     * @var int
     */
    public $precision = 0;

    /**
     * Return a value result showing the growth of an count aggregate over time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|string  $model
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     * @return \Jeanfprado\Metrics\Features\ValueResult
     */
    public function count($request, $model, $column = null, $dateColumn = null)
    {
        return $this->aggregate($request, $model, 'count', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of an average aggregate over time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|string  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Jeanfprado\Metrics\Features\ValueResult
     */
    public function average($request, $model, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $model, 'avg', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of a sum aggregate over time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|string  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Jeanfprado\Metrics\Features\ValueResult
     */
    public function sum($request, $model, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $model, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of a maximum aggregate over time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|string  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Jeanfprado\Metrics\Features\ValueResult
     */
    public function max($request, $model, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $model, 'max', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of a minimum aggregate over time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|string  $model
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string|null  $dateColumn
     * @return \Jeanfprado\Metrics\Features\ValueResult
     */
    public function min($request, $model, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $model, 'min', $column, $dateColumn);
    }

    /**
     * Return a value result showing the growth of a model over a given time frame.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|string  $model
     * @param  string  $function
     * @param  \Illuminate\Database\Query\Expression|string|null  $column
     * @param  string|null  $dateColumn
     * @return \Jeanfprado\Metrics\Features\ValueResult
     */
    protected function aggregate($request, $model, $function, $column = null, $dateColumn = null)
    {
        $query = $model instanceof Builder ? $model : (new $model())->newQuery();

        $column = $column ?? $query->getModel()->getQualifiedKeyName();

        if ($request->range === 'ALL') {
            return $this->result(
                round(with(clone $query)->{$function}($column), $this->precision)
            );
        }

        $previousValue = round(with(clone $query)->whereBetween(
            $dateColumn ?? $query->getModel()->getQualifiedCreatedAtColumn(),
            array_map(function ($datetime) {
                return $datetime;
            }, $this->previousRange($request->range))
        )->{$function}($column), $this->precision);

        return $this->result(
            round(with(clone $query)->whereBetween(
                $dateColumn ?? $query->getModel()->getQualifiedCreatedAtColumn(),
                array_map(function ($datetime) {
                    return $datetime;
                }, $this->currentRange($request->range))
            )->{$function}($column), $this->precision)
        )->previous($previousValue);
    }

    /**
     * Calculate the previous range and calculate any short-cuts.
     *
     * @param  string|int  $range
     * @return array
     */
    protected function previousRange($range)
    {
        if ($range == 'TODAY') {
            return [
                now()->modify('yesterday')->setTime(0, 0),
                today()->subSecond(1),
            ];
        }

        if ($range == 'MTD') {
            return [
                now()->modify('first day of previous month')->setTime(0, 0),
                now()->firstOfMonth()->subSecond(1),
            ];
        }

        if ($range == 'QTD') {
            return $this->previousQuarterRange();
        }

        if ($range == 'YTD') {
            return [
                now()->subYears(1)->firstOfYear()->setTime(0, 0),
                now()->firstOfYear()->subSecond(1),
            ];
        }

        return [
            now()->subDays($range * 2),
            now()->subDays($range)->subSecond(1),
        ];
    }

    /**
     * Calculate the previous quarter range.
     *
     * @param  string
     * @return array
     */
    protected function previousQuarterRange()
    {
        return [
            Carbon::firstDayOfPreviousQuarter(),
            Carbon::firstDayOfQuarter()->subSecond(1),
        ];
    }

    /**
     * Calculate the current range and calculate any short-cuts.
     *
     * @param  string|int  $range
     * @param  string
     * @return array
     */
    protected function currentRange($range)
    {
        if ($range == 'TODAY') {
            return [
                today(),
                now(),
            ];
        }

        if ($range == 'MTD') {
            return [
                now()->firstOfMonth(),
                now(),
            ];
        }

        if ($range == 'QTD') {
            return $this->currentQuarterRange();
        }

        if ($range == 'YTD') {
            return [
                now()->firstOfYear(),
                now(),
            ];
        }

        return [
            now()->subDays($range),
            now(),
        ];
    }

    /**
     * Calculate the previous quarter range.
     *
     * @param  string
     * @return array
     */
    protected function currentQuarterRange()
    {
        return [
            Carbon::firstDayOfQuarter(),
            now(),
        ];
    }

    /**
     * Set the precision level used when rounding the value.
     *
     * @param  int  $precision
     * @return $this
     */
    public function precision($precision = 0)
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * Create a new value metric result.
     *
     * @param  mixed  $value
     * @return \Jeanfprado\Metrics\Features\ValueResult
     */
    public function result($value)
    {
        return new ValueResult($value);
    }

    /**
     * Get a unique reference it's used to generate cache key
     *
     * @return string
     */
    protected function getUniqueReference()
    {
        //
    }
}
