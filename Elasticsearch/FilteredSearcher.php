<?php

namespace FlexModel\FlexModelElasticsearchBundle\Elasticsearch;

use Elasticsearch\Client;
use FlexModel\FlexModel;
use FlexModel\FlexModelElasticsearchBundle\ElasticsearchDSL\FiltersAggregation;
use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;
use ONGR\ElasticsearchDSL\Aggregation\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\GlobalAggregation;
use ONGR\ElasticsearchDSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermQuery;
use ONGR\ElasticsearchDSL\Search;

/**
 * FilteredSearcher.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class FilteredSearcher
{
    /**
     * The Elasticsearch client instance.
     *
     * @var Client
     */
    private $client;

    /**
     * The FlexModel instance.
     *
     * @var FlexModel
     */
    private $flexModel;

    /**
     * The name of the Elasticsearch index.
     *
     * @var string
     */
    private $indexName;

    /**
     * Constructs a new Indexer instance.
     *
     * @param Client    $client
     * @param FlexModel $flexModel
     * @param string    $indexName
     */
    public function __construct(Client $client, FlexModel $flexModel, $indexName)
    {
        $this->client = $client;
        $this->flexModel = $flexModel;
        $this->indexName = $indexName;
    }

    /**
     * Search for documents of the object name type with a query inside the Search instance.
     * Adds aggregations to populate the filters.
     *
     * @param string $objectName
     * @param Search $search
     *
     * @return array
     */
    public function search($objectName, Search $search)
    {
        $this->addSearchFilterAggregations($objectName, $search);

        $parameters = array(
            'index' => $this->indexName,
            'type' => $objectName,
            'body' => $search->toArray(),
        );

        return $this->client->search($parameters);
    }

    /**
     * Adds aggregations to the Search instance based on the 'searchfilters' form of the object.
     *
     * @param string $objectName
     * @param Search $search
     */
    private function addSearchFilterAggregations($objectName, Search $search)
    {
        $formConfiguration = $this->flexModel->getFormConfiguration($objectName, 'searchfilters');
        if (is_array($formConfiguration)) {
            $globalAggregation = new GlobalAggregation('all');

            foreach ($formConfiguration['fields'] as $formFieldConfiguration) {
                $fieldConfiguration = $this->flexModel->getField($objectName, $formFieldConfiguration['name']);

                $aggregation = null;
                switch ($fieldConfiguration['datatype']) {
                    case 'BOOLEAN':
                        $aggregation = new FiltersAggregation($fieldConfiguration['name']);
                        $aggregation->setField($fieldConfiguration['name']);
                        $aggregation->addFilter(new TermQuery($fieldConfiguration['name'], true), 'true');
                        $aggregation->addFilter(new TermQuery($fieldConfiguration['name'], false), 'false');
                        $aggregation->addAggregation(new ValueCountAggregation($fieldConfiguration['name'], $fieldConfiguration['name']));
                        break;
                    case 'SET':
                    case 'VARCHAR':
                        if (isset($fieldConfiguration['options'])) {
                            $aggregation = new TermsAggregation($fieldConfiguration['name'], $fieldConfiguration['name']);
                        }
                        break;
                }

                if ($aggregation instanceof AbstractAggregation) {
                    $filter = new FilterAggregation($aggregation->getField());
                    $filterQuery = new BoolQuery();
                    if ($search->getQueries() instanceof BoolQuery) {
                        foreach ($search->getQueries()->getQueries() as $query) {
                            $filterFieldName = current(array_keys($query->toArray()[$query->getType()]));
                            if ($aggregation->getField() !== $filterFieldName) {
                                $filterQuery->add($query);
                            }
                        }
                    }
                    $filter->setFilter($filterQuery);
                    $filter->addAggregation($aggregation);
                    if (empty($filter->getArray()['bool'])) {
                        $filterQuery->add(new MatchAllQuery());
                    }

                    $aggregation = $filter;

                    $globalAggregation->addAggregation($aggregation);
                }
            }

            $search->addAggregation($globalAggregation);
        }
    }
}
