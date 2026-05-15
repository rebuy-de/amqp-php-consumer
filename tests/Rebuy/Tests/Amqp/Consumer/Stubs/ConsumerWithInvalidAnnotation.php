<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithInvalidAnnotation
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer
     */
    public function consume(Message $message): void
    {
    }
}
