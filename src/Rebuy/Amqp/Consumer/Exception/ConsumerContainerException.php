<?php

namespace Rebuy\Amqp\Consumer\Exception;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\Message\MessageInterface;
use RuntimeException;
use Throwable;

class ConsumerContainerException extends RuntimeException
{
    /**
     * @var MessageInterface
     */
    private $payloadMessage;

    /**
     * @var ConsumerContainer
     */
    private $consumerContainer;

    /**
     * @var AMQPMessage
     */
    private $amqpMessage;

    public function __construct(
        ConsumerContainer $consumerContainer,
        AMQPMessage $amqpMessage,
        MessageInterface $payloadMessage,
        Throwable $e
    )
    {
        $this->payloadMessage = $payloadMessage;
        $this->amqpMessage = $amqpMessage;
        $this->consumerContainer = $consumerContainer;

        parent::__construct($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @return AMQPMessage
     */
    public function getAmqpMessage()
    {
        return $this->amqpMessage;
    }

    /**
     * @return MessageInterface
     */
    public function getPayloadMessage()
    {
        return $this->payloadMessage;
    }

    /**
     * @return ConsumerContainer
     */
    public function getConsumerContainer()
    {
        return $this->consumerContainer;
    }
}
