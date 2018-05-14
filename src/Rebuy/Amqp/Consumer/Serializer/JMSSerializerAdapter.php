<?php

namespace Rebuy\Amqp\Consumer\Serializer;

use JMS\Serializer\SerializerInterface;

class JMSSerializerAdapter implements Serializer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    
    public function deserialize($data, $type, $format)
    {
        $this->serializer->deserialize($data, $type, $format);
    }
}
