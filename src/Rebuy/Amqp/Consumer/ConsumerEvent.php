<?php

namespace Rebuy\Amqp\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Attribute\ConsumerContainer;
use Symfony\Contracts\EventDispatcher\Event;

class ConsumerEvent extends Event
{
    public function __construct(
        private readonly AMQPMessage $message,
        private readonly ConsumerContainer $consumerContainer,
    ) {
    }

    public function getMessage(): AMQPMessage
    {
        return $this->message;
    }

    public function getConsumerContainer(): ConsumerContainer
    {
        return $this->consumerContainer;
    }
}
