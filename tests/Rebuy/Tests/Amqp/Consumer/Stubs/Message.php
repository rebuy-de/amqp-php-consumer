<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

use Rebuy\Amqp\Consumer\Message\MessageInterface;

class Message implements MessageInterface
{
    public static function getName()
    {
        return 'genericMessage';
    }
}
