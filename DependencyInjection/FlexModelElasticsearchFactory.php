<?php

namespace FlexModel\FlexModelElasticsearchBundle\DependencyInjection;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

/**
 * FlexModelElasticsearchFactory.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class FlexModelElasticsearchFactory
{
    /**
     * Creates a new Elasticsearch Client instance.
     *
     * @param array $hosts
     *
     * @return Client
     */
    public static function createElasticsearchClient(array $hosts)
    {
        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        return $client;
    }
}
