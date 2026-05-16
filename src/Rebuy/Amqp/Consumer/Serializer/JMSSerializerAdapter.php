<?php

namespace Rebuy\Amqp\Consumer\Serializer;

use JMS\Serializer\SerializerInterface;

class JMSSerializerAdapter implements Serializer
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function deserialize($data, $type, $format)
    {
        return $this->serializer->deserialize($data, $type, $format);
    }
}
