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
     * @param string      $objectName
     * @param array       $searchResult
     * @param string|null $formName
     */
    public function parse($objectName, array $searchResult, $formName = null)
    {
        $aggregationResults = array();
        if (isset($searchResult['aggregations']['all'])) {
            foreach ($searchResult['aggregations']['all'] as $aggregationField => $aggregation) {
                if (isset($aggregation[$aggregationField]['buckets'])) {
                    $fieldConfiguration = $this->flexModel->getField($objectName, $aggregationField);
                    $formFieldConfiguration = $this->getFormFieldConfiguration($objectName, $formName, $aggregationField);

                    $aggregationResult = new AggregationResultCollection($aggregationField, $this->getFieldLabel($formFieldConfiguration, $fieldConfiguration));

                    $bucket = $aggregation[$aggregationField]['buckets'];
                    $this->addMissingOptionsToBucket($formFieldConfiguration, $fieldConfiguration, $bucket);
                    foreach ($bucket as $value => $bucketItem) {
                        if (isset($bucketItem['key'])) {
                            $value = $bucketItem['key'];
                        }
                        $label = $this->getAggregationResultLabel($formFieldConfiguration, $fieldConfiguration, $value);

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
     * @param array $formFieldConfiguration
     * @param array $fieldConfiguration
     * @param array $bucket
     */
    private function addMissingOptionsToBucket(array $formFieldConfiguration, array $fieldConfiguration, array &$bucket)
    {
        $options = null;
        if (isset($formFieldConfiguration['options'])) {
            $options = $formFieldConfiguration['options'];
        } elseif (isset($fieldConfiguration['options'])) {
            $options = $fieldConfiguration['options'];
        }

        if (isset($options)) {
            $updatedBucket = array();
            foreach ($options as $option) {
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
     * Returns the field configuration in the specified form.
     *
     * @param string      $objectName
     * @param string|null $formName
     * @param string      $fieldName
     *
     * @return array
     */
    private function getFormFieldConfiguration($objectName, $formName, $fieldName)
    {
        $formConfiguration = $this->flexModel->getFormConfiguration($objectName, $formName);
        if (isset($formConfiguration)) {
            foreach ($formConfiguration['fields'] as $formFieldConfiguration) {
                if ($formFieldConfiguration['name'] === $fieldName) {
                    return $formFieldConfiguration;
                }
            }
        }

        return array();
    }

    /**
     * Returns the label of the field.
     *
     * @param array $formFieldConfiguration
     * @param array $fieldConfiguration
     *
     * @return string
     */
    private function getFieldLabel(array $formFieldConfiguration, array $fieldConfiguration)
    {
        $label = '';
        if (isset($formFieldConfiguration['label'])) {
            $label = $formFieldConfiguration['label'];
        } elseif (isset($fieldConfiguration['label'])) {
            $label = $fieldConfiguration['label'];
        }

        return $label;
    }

    /**
     * Returns the label for the aggregation result item.
     *
     * @param array $formFieldConfiguration
     * @param array $fieldConfiguration
     * @param mixed $value
     *
     * @return string
     */
    private function getAggregationResultLabel(array $formFieldConfiguration, array $fieldConfiguration, $value)
    {
        $label = '';
        if (isset($fieldConfiguration['datatype']) && $fieldConfiguration['datatype'] === 'BOOLEAN') {
            $label = 'No';
            if ($value === 'true') {
                $label = 'Yes';
            }
        }

        if (isset($formFieldConfiguration['options'])) {
            $label = $this->getAggregationResultLabelFromOptions($formFieldConfiguration['options'], $value);
        } elseif (isset($fieldConfiguration['options'])) {
            $label = $this->getAggregationResultLabelFromOptions($fieldConfiguration['options'], $value);
        }

        return $label;
    }

    /**
     * Returns the label from the options based on the specified option value.
     *
     * @param array $options
     * @param mixed $value
     *
     * @return array
     */
    private function getAggregationResultLabelFromOptions(array $options, $value)
    {
        $label = '';
        foreach ($options as $option) {
            if ($option['value'] === $value) {
                $label = $option['label'];
                break;
            }
        }

        return $label;
    }
}
