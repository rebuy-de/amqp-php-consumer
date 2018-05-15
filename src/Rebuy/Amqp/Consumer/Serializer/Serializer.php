<?php

namespace Rebuy\Amqp\Consumer\Serializer;

interface Serializer
{
    public function deserialize($data, $type, $format);
}
