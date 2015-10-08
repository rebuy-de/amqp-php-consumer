<?php

namespace Rebuy\Amqp\Consumer;

use InvalidArgumentException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use PhpAmqpLib\Channel\AMQPChannel;
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
