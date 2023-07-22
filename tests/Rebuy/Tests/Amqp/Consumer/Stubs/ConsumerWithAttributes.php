<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithAttributes
{
    #[\Rebuy\Amqp\Consumer\Annotation\Consumer(name: 'consume-with-attributes', prefetchCount: 100)]
    public function consume(Message $message)
    {
    }
}
