<?php

namespace Rebuy\Amqp\Consumer\Exception;

use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\Message\MessageInterface;
use RuntimeException;
use Throwable;

class ConsumerContainerException extends RuntimeException
{
    public function __construct(
        private readonly ConsumerContainer $consumerContainer,
        private readonly AMQPMessage $amqpMessage,
        private readonly MessageInterface $payloadMessage,
        Throwable $previous,
    ) {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }

    public function getAmqpMessage(): AMQPMessage
    {
        return $this->amqpMessage;
    }

    public function getPayloadMessage(): MessageInterface
    {
        return $this->payloadMessage;
    }

    public function getConsumerContainer(): ConsumerContainer
    {
        return $this->consumerContainer;
    }
}
