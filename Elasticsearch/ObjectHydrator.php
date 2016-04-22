<?php

namespace FlexModel\FlexModelElasticsearchBundle\Elasticsearch;

use FlexModel\FlexModelElasticsearchBundle\Serializer\Encoder\ArrayDecoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * ObjectHydrator.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ObjectHydrator
{
    /**
     * The Serializer instance.
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructs a new ObjectHydrator.
     */
    public function __construct()
    {
        $normalizer = new PropertyNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $decoder = new ArrayDecoder();

        $this->serializer = new Serializer(array($normalizer), array($decoder));
    }

    /**
     * Hydrates the data into an instance of the specified class name.
     *
     * @param string $objectClassName
     * @param array  $data
     *
     * @return object
     */
    public function hydrate($objectClassName, array $data)
    {
        return $this->serializer->deserialize($data, $objectClassName, 'array');
    }

    /**
     * Hydrates an array containing data for multiple objects into instances of the specified class name.
     *
     * @param string $objectClassName
     * @param array  $data
     *
     * @return object[]
     */
    public function hydrateAll($objectClassName, array $data)
    {
        $objects = array();
        foreach ($data as $objectData) {
            $objects[] = $this->hydrate($objectClassName, $objectData);
        }

        return $objects;
    }
}
