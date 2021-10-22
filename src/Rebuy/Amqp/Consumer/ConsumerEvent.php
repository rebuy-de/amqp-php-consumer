<?php

namespace Rebuy\Amqp\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Symfony\Contracts\EventDispatcher\Event;

class ConsumerEvent extends Event
{
    /**
     * @var AMQPMessage
     */
    private $envelope;

    /**
     * @var ConsumerContainer
     */
    private $consumerContainer;

    /**
     * @param AMQPMessage $message
     * @param ConsumerContainer $consumerContainer
     */
    public function __construct(AMQPMessage $message, ConsumerContainer $consumerContainer)
    {
        $this->envelope = $message;
        $this->consumerContainer = $consumerContainer;
    }

    /**
     * @return AMQPMessage
     */
    public function getMessage()
    {
        return $this->envelope;
    }

    /**
     * @return ConsumerContainer
     */
    public function getConsumerContainer()
    {
        return $this->consumerContainer;
    }
}
