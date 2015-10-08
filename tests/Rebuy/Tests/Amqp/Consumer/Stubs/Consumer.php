<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class Consumer
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume-it")
     * @param Message $message
     */
    public function consume(Message $message)
    {
    }

    public function methodWithoutAnnotation()
    {
    }
}
