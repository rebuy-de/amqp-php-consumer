<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithInvalidAnnotation
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer
     *
     * @param Message $message
     */
    public function consume(Message $message)
    {
    }
}
