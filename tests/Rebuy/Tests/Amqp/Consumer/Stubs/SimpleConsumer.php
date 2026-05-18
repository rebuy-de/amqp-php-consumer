<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

use Rebuy\Amqp\Consumer\Attribute\Consumer;

class SimpleConsumer
{
    public int $invocationCount = 0;

    #[Consumer(name: 'consume-it')]
    public function consume(Message $message): void
    {
        ++$this->invocationCount;
    }

    public function methodWithoutAttribute(): void
    {
    }
}
