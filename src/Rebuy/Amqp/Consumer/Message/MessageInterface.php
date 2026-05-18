<?php

namespace Rebuy\Amqp\Consumer\Message;

interface MessageInterface
{
    public static function getRoutingKey(): string;
}
