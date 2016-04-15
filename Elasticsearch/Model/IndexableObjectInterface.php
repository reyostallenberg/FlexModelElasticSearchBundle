<?php

namespace FlexModel\FlexModelElasticsearchBundle\Elasticsearch\Model;

/**
 * IndexObjectInterface.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
interface IndexableObjectInterface
{
    /**
     * Returns the id of the object.
     */
    public function getId();
}
