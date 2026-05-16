<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

use Rebuy\Amqp\Consumer\Annotation\Consumer;

class ConsumerWithPrefetchCount
{
    #[Consumer(name: 'consume', prefetchCount: 100)]
    public function consume(Message $message): void
    {
    }
}
