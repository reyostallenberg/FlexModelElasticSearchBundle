<?php

namespace FlexModel\FlexModelElasticsearchBundle\ElasticsearchDSL;

use LogicException;
use ONGR\ElasticsearchDSL\Aggregation\FiltersAggregation as BaseFiltersAggregation;
use ONGR\ElasticsearchDSL\BuilderInterface;

/**
 * FiltersAggregation.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class FiltersAggregation extends BaseFiltersAggregation
{
    /**
     * {@inheritdoc}
     */
    public function addFilter(BuilderInterface $filter, $name = '')
    {
        if ($this->anonymous === false && empty($name)) {
            throw new LogicException('In not anonymous filters filter name must be set.');
        } elseif ($this->anonymous === false && !empty($name)) {
            $this->filters['filters'][$name] = $filter->toArray();
        } else {
            $this->filters['filters'][] = $filter->toArray();
        }

        return $this;
    }
}
