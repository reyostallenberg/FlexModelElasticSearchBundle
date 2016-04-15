<?php

namespace FlexModel\FlexModelElasticsearchBundle\Elasticsearch;

use Elasticsearch\Client;
use FlexModel\FlexModel;
use FlexModel\FlexModelElasticsearchBundle\Elasticsearch\Model\IndexableObjectInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Container;

/**
 * Indexer.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class Indexer
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
     * Adds an object to the Elasticsearch index.
     *
     * @param IndexableObjectInterface $object
     */
    public function indexObject(IndexableObjectInterface $object)
    {
        $reflectionClass = new ReflectionClass($object);
        $objectName = $reflectionClass->getShortName();

        $parameters = array(
            'index' => $this->indexName,
            'type' => $objectName,
            'id' => $object->getId(),
            'body' => array(),
        );

        $fieldNames = $this->flexModel->getFieldNamesByView($objectName, 'searchindex');
        foreach ($fieldNames as $fieldName) {
            $getter = 'get'.Container::camelize($fieldName);

            $parameters['body'][$fieldName] = $object->$getter();
        }

        $this->client->index($parameters);
    }
}
