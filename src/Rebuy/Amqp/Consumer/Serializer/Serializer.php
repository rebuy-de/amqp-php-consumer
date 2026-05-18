<?php

namespace Rebuy\Amqp\Consumer\Serializer;

interface Serializer
{
    /**
     * @template TObject of object
     * @template TType of string|class-string<TObject>
     *
     * @param TType $type
     *
     * @phpstan-return ($type is class-string<TObject> ? TObject : mixed)
     *
     * @psalm-return (TType is class-string<TObject> ? TObject : mixed)
     */
    public function deserialize(mixed $data, string $type, string $format): mixed;
}
