<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

use Rebuy\Amqp\Consumer\Attribute\Consumer;

class ConsumerWithTwoParameters
{
    #[Consumer(name: 'consume')]
    public function consume(Message $message, Message $message2): void
    {
    }
}
