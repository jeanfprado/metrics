<?php

namespace Jeanfprado\Metric\Features;

use JsonSerializable;

class ValueResult implements JsonSerializable
{
    /**
     * The value of the result.
     *
     * @var mixed
     */
    public $value;

    /**
     * The previous value.
     *
     * @var mixed
     */
    public $previous;

    /**
     * The previous value label.
     *
     * @var string
     */
    public $previousLabel;

    /**
     * The metric value prefix.
     *
     * @var string
     */
    public $prefix;

    /**
     * The metric value suffix.
     *
     * @var string
     */
    public $suffix;

    /**
     * The metric value formatting.
     *
     * @var string
     */
    public $format;

    /**
     * Determines whether a value of 0 counts as "No Current Data".
     *
     * @var bool
     */
    public $zeroResult = false;

    /**
     * Create a new value result instance.
     *
     * @param  mixed  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Set the previous value for the metric.
     *
     * @param  mixed  $previous
     * @param  string  $label
     * @return self
     */
    public function previous($previous, $label = null)
    {
        $this->previous = $previous;
        $this->previousLabel = $label;

        return $this;
    }

    /**
     * Set the metric value prefix.
     *
     * @param  string  $prefix
     * @return self
     */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set the metric value suffix.
     *
     * @param  string  $suffix
     * @return self
     */
    public function suffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Set the metric value formatting.
     *
     * @param  string  $format
     * @return self
     */
    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Sets the zeroResult value.
     *
     * @param  bool  $zeroResult
     * @return self
     */
    public function allowZeroResult($zeroResult = true)
    {
        $this->zeroResult = $zeroResult;

        return $this;
    }

    /**
     * Prepare the metric result for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'value' => $this->value,
            'previous' => $this->previous,
            'previousLabel' => $this->previousLabel,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'format' => $this->format,
            'zeroResult' => $this->zeroResult,
        ];
    }
}
