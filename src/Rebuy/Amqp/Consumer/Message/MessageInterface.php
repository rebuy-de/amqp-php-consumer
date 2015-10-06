<?php

namespace Rebuy\Amqp\Consumer\Message;

interface MessageInterface
{
    /**
     * @return string
     */
    public static function getName();
}
