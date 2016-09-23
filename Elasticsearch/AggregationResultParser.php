<?php

namespace FlexModel\FlexModelElasticsearchBundle\Elasticsearch;

use FlexModel\FlexModel;

/**
 * AggregationResultParser.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class AggregationResultParser
{
    /**
     *
     * @var FlexModel
     */
    private $flexModel;

    /**
     * Constructs a new AggregationResultParser instance.
     *
     * @param FlexModel $flexModel
     */
    public function __construct(FlexModel $flexModel)
    {
        $this->flexModel = $flexModel;
    }

    /**
     * Parses the aggregations from the Elasticsearch search result.
     *
     * @param string $objectName
     * @param array  $searchResult
     */
    public function parse($objectName, array $searchResult)
    {
        $aggregationResults = array();
        if (isset($searchResult['aggregations']['all'])) {
            foreach ($searchResult['aggregations']['all'] as $aggregationField => $aggregation) {
                if (isset($aggregation[$aggregationField]['buckets'])) {
                    $fieldConfiguration = $this->flexModel->getField($objectName, $aggregationField);

                    $aggregationResult = new AggregationResultCollection($aggregationField, $this->getFieldLabel($fieldConfiguration));

                    $bucket = $aggregation[$aggregationField]['buckets'];
                    $this->addMissingOptionsToBucket($fieldConfiguration, $bucket);
                    foreach ($bucket as $value => $bucketItem) {
                        if (isset($bucketItem['key'])) {
                            $value = $bucketItem['key'];
                        }
                        $label = $this->getAggregationResultLabel($fieldConfiguration, $value);

                        $aggregationResult[] = new AggregationResult($label, $value, $bucketItem['doc_count']);
                    }

                    $aggregationResults[$aggregationField] = $aggregationResult;
                }
            }
        }

        return $aggregationResults;
    }

    /**
     * Adds missing options from the field configuration to the bucket.
     *
     * @param array $fieldConfiguration
     * @param array $bucket
     */
    private function addMissingOptionsToBucket(array $fieldConfiguration, array &$bucket)
    {
        if (isset($fieldConfiguration['options'])) {
            $updatedBucket = array();
            foreach ($fieldConfiguration['options'] as $option) {
                $updatedBucketItem = array(
                    'key' => $option['value'],
                    'doc_count' => 0,
                );

                foreach ($bucket as $value => $bucketItem) {
                    if (isset($bucketItem['key'])) {
                        $value = $bucketItem['key'];
                    }
                    if ($value === $option['value']) {
                        $updatedBucketItem = $bucketItem;
                    }
                }

                $updatedBucket[$option['value']] = $updatedBucketItem;
            }

            $bucket = $updatedBucket;
        }
    }

    /**
     * Returns the label of the field.
     *
     * @param array $fieldConfiguration
     *
     * @return string
     */
    private function getFieldLabel(array $fieldConfiguration)
    {
        $label = '';
        if (isset($fieldConfiguration['label'])) {
            $label = $fieldConfiguration['label'];
        }

        return $label;
    }

    /**
     *
     * @param array $fieldConfiguration
     * @param mixed $value
     *
     * @return string
     */
    private function getAggregationResultLabel(array $fieldConfiguration, $value)
    {
        $label = '';
        if (isset($fieldConfiguration['datatype']) && $fieldConfiguration['datatype'] === 'BOOLEAN') {
            $label = 'No';
            if ($value === 'true') {
                $label = 'Yes';
            }
        }

        if (isset($fieldConfiguration['options'])) {
            foreach ($fieldConfiguration['options'] as $option) {
                if ($option['value'] === $value) {
                    $label = $option['label'];
                    break;
                }
            }
        }

        return $label;
    }
}
