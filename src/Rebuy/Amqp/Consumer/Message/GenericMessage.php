<?php

namespace Rebuy\Amqp\Consumer\Message;

interface GenericMessage
{
    /**
     * @return string
     */
    public static function getName();
}
