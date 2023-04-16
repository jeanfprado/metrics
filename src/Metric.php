<?php

namespace Jeanfprado\Metric;

use DateInterval;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{View, Cache};

abstract class Metric
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $id;

    /**
    * Stores the available chart letters to create the ID.
    *
    * @var string
    */
    private $chartLetters = 'abcdefghijklmnopqrstuvwxyz';

    public function __construct()
    {
        $this->script = 'metrics::script';
        $this->id = substr(str_shuffle(str_repeat($x = $this->chartLetters, ceil(25 / strlen($x)))), 1, 25);
    }

    /**
     * Calculate the metric's value.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function resolve(Request $request)
    {
        $resolver = function () use ($request) {
            return $this->calculate($request);
        };

        if ($cacheFor = $this->cacheFor()) {
            $cacheFor = is_numeric($cacheFor) ? new DateInterval(sprintf('PT%dS', $cacheFor * 60)) : $cacheFor;

            return Cache::remember(
                $this->getCacheKey($request),
                $cacheFor,
                $resolver
            );
        }

        return $resolver();
    }

    /**
     * Get the appropriate cache key for the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return string
     */
    protected function getCacheKey(Request $request)
    {
        return sprintf(
            'jeanfprado.metric.%s.%s.%s',
            $this->uriKey(),
            $request->input('range', 'no-range'),
            $this->getUniqueReference() ?: 'no-unique-reference',
        );
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        //
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return Str::slug($this->name(), '-', null);
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return $this->name ?: Str::title(Str::snake(class_basename(get_class($this)), ' '));
    }

    /**
     * Set the chart script.
     *
     * @param string $script
     *
     * @return self
     */
    public function script(string $script = null)
    {
        if (!$script) {
            return View::make($this->script, ['metric' => $this]);
        }

        $this->script = $script;

        return $this;
    }

    abstract protected function getUniqueReference();
}
