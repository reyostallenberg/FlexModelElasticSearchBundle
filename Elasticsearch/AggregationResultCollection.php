<?php

namespace FlexModel\FlexModelElasticsearchBundle\Elasticsearch;

use ArrayIterator;

/**
 * AggregationResultCollection.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class AggregationResultCollection extends ArrayIterator
{
    /**
     * The field name of the aggregation results.
     *
     * @var string
     */
    private $fieldName;

    /**
     * The field label of the aggregation results.
     *
     * @var string
     */
    private $fieldLabel;

    /**
     * Constructs a new AggregationResultCollection instance.
     *
     * @param string $fieldName
     * @param string $fieldLabel
     * @param array  $array
     * @param int    $flags
     */
    public function __construct($fieldName, $fieldLabel, array $array = array(), $flags = 0)
    {
        parent::__construct($array, $flags);

        $this->fieldName = $fieldName;
        $this->fieldLabel = $fieldLabel;
    }

    /**
     * Returns the field name of the aggregation results.
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Returns the field label of the aggregation results.
     *
     * @return string
     */
    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }
}
