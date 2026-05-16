<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class Consumer
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume-it")
     */
    public function consume(Message $message): void
    {
    }

    public function methodWithoutAnnotation(): void
    {
    }
}
