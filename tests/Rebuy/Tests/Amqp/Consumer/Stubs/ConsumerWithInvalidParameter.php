<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

use Rebuy\Amqp\Consumer\Annotation\Consumer;

class ConsumerWithInvalidParameter
{
    #[Consumer(name: 'consume')]
    public function consume($message): void
    {
    }

    #[Consumer(name: 'consume')]
    public function classWithoutImplementingInterface(\stdClass $message): void
    {
    }
}
