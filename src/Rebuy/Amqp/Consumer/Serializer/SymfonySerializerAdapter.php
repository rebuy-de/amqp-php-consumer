<?php

namespace Rebuy\Amqp\Consumer\Serializer;

use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerAdapter implements Serializer
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function deserialize(mixed $data, string $type, string $format): mixed
    {
        return $this->serializer->deserialize($data, $type, $format);
    }
}
