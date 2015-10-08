<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

class ConsumerWithInvalidParameter
{
    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume")
     * @param mixed $message
     */
    public function consume($message)
    {
    }

    /**
     * @Rebuy\Amqp\Consumer\Annotation\Consumer(name="consume")
     * @param \stdClass $message
     */
    public function classWithoutImplementingInterface(\stdClass $message)
    {
    }
}
