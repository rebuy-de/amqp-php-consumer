<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithInvalidParameter
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume")
     */
    public function consume($message): void
    {
    }

    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume")
     */
    public function classWithoutImplementingInterface(\stdClass $message): void
    {
    }
}
