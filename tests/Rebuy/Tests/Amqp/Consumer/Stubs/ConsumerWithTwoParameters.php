<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithTwoParameters
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume")
     */
    public function consume(Message $message, Message $message2): void
    {
    }
}
