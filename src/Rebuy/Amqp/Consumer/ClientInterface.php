<?php

namespace Rebuy\Amqp\Consumer;

use InvalidArgumentException;
use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Message\MessageInterface;

interface ClientInterface
{
    /**
     * @param MessageInterface|AMQPMessage $message
     * @param string $routingKey
     *
     * @throws InvalidArgumentException
     */
    public function sendMessage($message, $routingKey = null);
}
