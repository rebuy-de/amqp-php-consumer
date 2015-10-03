<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithPrefetchCount
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume", prefetchCount=100)
     * @param Message $message
     */
    public function consume(Message $message)
    {
    }
}
