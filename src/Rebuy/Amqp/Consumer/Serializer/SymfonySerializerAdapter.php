<?php

namespace Rebuy\Amqp\Consumer\Serializer;

use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerAdapter implements Serializer
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
        return $this->serializer->deserialize($data, $type, $format);
    }
}
