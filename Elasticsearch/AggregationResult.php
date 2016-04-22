<?php

namespace FlexModel\FlexModelElasticsearchBundle\Elasticsearch;

/**
 * AggregationResult.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class AggregationResult
{
    /**
     * The label of the aggregation result.
     *
     * @var string
     */
    private $label;

    /**
     * The value of the aggregation result.
     *
     * @var mixed
     */
    private $value;

    /**
     * The count of the aggregation result.
     *
     * @var int
     */
    private $count;

    /**
     * Constructs a new AggregationResult instance.
     *
     * @param string $label
     * @param mixed  $value
     * @param int    $count
     */
    public function __construct($label, $value, $count)
    {
        $this->label = $label;
        $this->value = $value;
        $this->count = $count;
    }

    /**
     * Returns the label of the aggregation result.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the value of the aggregation result.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the count of the aggregation result.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
