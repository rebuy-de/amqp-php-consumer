<?php

namespace Rebuy\Tests\Amqp\Consumer\Stubs;

use Rebuy\Amqp\Consumer\Message\GenericMessage;

class Message implements GenericMessage
{
    public static function getName()
    {
        return 'genericMessage';
    }
}
