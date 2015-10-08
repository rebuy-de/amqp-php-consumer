<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithTwoParameters
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume")
     * @param Message $message
     * @param Message $message2
     */
    public function consume(Message $message, Message $message2)
    {
    }
}
