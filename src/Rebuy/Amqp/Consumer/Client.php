<?php

namespace Rebuy\Amqp\Consumer;

use InvalidArgumentException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Message\GenericMessage;

class Client
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @param AMQPChannel $channel
     * @param string $exchange
     * @param Serializer $serializer
     */
    public function __construct(AMQPChannel $channel, $exchange, Serializer $serializer)
    {
        $this->channel = $channel;
        $this->serializer = $serializer;
        $this->exchange = $exchange;
    }

    /**
     * @param GenericMessage|AMQPMessage $message
     * @param string $routingKey
     *
     * @throws InvalidArgumentException
     */
    public function sendMessage($message, $routingKey = null)
    {
        if (!($message instanceof GenericMessage) && !($message instanceof AMQPMessage)) {
            $exMessage = sprintf(
                'The message must be an instance of %s or %s, but got %s',
                GenericMessage::class,
                AMQPMessage::class,
                gettype($message)
            );
            throw new InvalidArgumentException($exMessage);
        }

        if ($message instanceof GenericMessage) {
            $routingKey = $routingKey ?: $message->getName();
            $message = $this->createMessage($message);
        }

        $this->channel->basic_publish($message, $this->exchange, $routingKey);
    }

    /**
     * @param GenericMessage $message
     *
     * @return string
     */
    private function getPayload(GenericMessage $message)
    {
        return $this->serializer->serialize($message, 'json', $this->getSerializationContext());
    }

    /**
     * @return SerializationContext
     */
    private function getSerializationContext()
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return $context;
    }

    /**
     * @param GenericMessage $message
     *
     * @return AMQPMessage
     */
    private function createMessage(GenericMessage $message)
    {
        $properties = [];
        $properties['content_encoding'] = 'UTF-8';
        $properties['content_type'] = 'text/plain';
        $properties['delivery_mode'] = 2;

        $properties['application_headers'] = [];
        $properties['application_headers']['type'] = ['S', $message->getName()];

        $payload = $this->getPayload($message);

        return new AMQPMessage($payload, $properties);
    }
}
