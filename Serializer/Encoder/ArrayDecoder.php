<?php

namespace FlexModel\FlexModelElasticsearchBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * 'Decodes' array data.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ArrayDecoder implements DecoderInterface
{
    /**
     * The supported decoder format.
     */
    const FORMAT = 'array';

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = array())
    {
        $decodedData = array();
        if (isset($data['_source'])) {
            $decodedData = $data['_source'];
        }
        if (isset($data['_id'])) {
            $decodedData['id'] = $data['_id'];
        }

        return $decodedData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return self::FORMAT === $format;
    }
}
