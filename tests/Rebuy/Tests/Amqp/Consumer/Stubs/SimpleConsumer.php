<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

use Rebuy\Amqp\Consumer\Annotation\Consumer;

class SimpleConsumer
{
    #[Consumer(name: 'consume-it')]
    public function consume(Message $message): void
    {
    }

    public function methodWithoutAnnotation(): void
    {
    }
}
