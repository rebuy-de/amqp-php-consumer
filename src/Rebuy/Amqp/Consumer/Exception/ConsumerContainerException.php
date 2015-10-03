<?php

namespace Rebuy\Amqp\Consumer\Exception;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\Message\GenericMessage;
use RuntimeException;

class ConsumerContainerException extends RuntimeException
{
    /**
     * @var GenericMessage
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

    /**
     * @param ConsumerContainer $consumerContainer
     * @param AMQPMessage $amqpMessage
     * @param GenericMessage $payloadMessage
     * @param Exception $e
     */
    public function __construct(
        ConsumerContainer $consumerContainer,
        AMQPMessage $amqpMessage,
        GenericMessage $payloadMessage,
        Exception $e
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
     * @return GenericMessage
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
