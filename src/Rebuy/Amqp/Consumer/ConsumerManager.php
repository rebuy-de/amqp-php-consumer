<?php

namespace Rebuy\Amqp\Consumer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\Annotation\Parser;
use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;
use Rebuy\Amqp\Consumer\Exception\ConsumerException;
use Rebuy\Amqp\Consumer\Handler\ErrorHandlerInterface;
use Rebuy\Amqp\Consumer\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Throwable;

class ConsumerManager
{
    public const DEFAULT_IDLE_TIMEOUT = 900;

    /**
     * @var ConsumerContainer[]
     */
    private $consumerContainers;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $exchangeName;

    /**
     * @var Collection
     */
    private $errorHandlers;

    /**
     * @var int
     */
    private $idleTimeout = self::DEFAULT_IDLE_TIMEOUT;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param string $exchangeName
     */
    public function __construct(AMQPChannel $channel, $exchangeName, Serializer $serializer, Parser $parser)
    {
        $this->serializer = $serializer;
        $this->eventDispatcher = new EventDispatcher();
        $this->channel = $channel;
        $this->exchangeName = $exchangeName;

        $this->consumerContainers = [];
        $this->errorHandlers = new ArrayCollection();
        $this->parser = $parser;
    }

    public function wait(): void
    {
        while (count($this->channel->callbacks)) {
            $this->channel->wait(null, false, $this->idleTimeout);
        }
    }

    /**
     * @param object $consumer
     *
     * @throws ConsumerException
     */
    public function registerConsumer($consumer): void
    {
        $type = gettype($consumer);
        if ('object' !== $type) {
            throw new ConsumerException(sprintf('Expected argument of type "object", "%s" given', $type));
        }

        $consumerContainers = $this->parser->getConsumerMethods($consumer);
        foreach ($consumerContainers as $consumerContainer) {
            $this->registerConsumerContainer($consumerContainer);
        }
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws ConsumerException
     */
    private function registerConsumerContainer(ConsumerContainer $consumerContainer): void
    {
        $consumerName = $consumerContainer->getConsumerName();
        if (isset($this->consumerContainers[$consumerName])) {
            $currentConsumer = $this->consumerContainers[$consumerName];
            $message = sprintf(
                'Can not register consumer method [%s] because the consumer method [%s] already uses that name',
                $consumerContainer->getMethodName(),
                $currentConsumer->getMethodName()
            );

            throw new ConsumerException($message);
        }

        $this->channel->queue_declare($consumerName, false, true, false, false);
        foreach ($consumerContainer->getBindings() as $binding) {
            $this->channel->queue_bind($consumerName, $this->exchangeName, $binding);
        }

        $this->channel->basic_qos(null, $consumerContainer->getPrefetchCount(), false);
        $this->channel->basic_consume($consumerName, '', false, false, false, false, function (AMQPMessage $message) use ($consumerContainer) {
            $this->consume($consumerContainer, $message);
            $message->getChannel()->basic_ack($message->getDeliveryTag());
        });

        $this->consumerContainers[$consumerName] = $consumerContainer;
    }

    public function registerErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandlers->add($errorHandler);
    }

    /**
     * @param int $idleTimeout
     */
    public function setIdleTimeout($idleTimeout): void
    {
        $this->idleTimeout = $idleTimeout;
    }

    /**
     * @return int
     */
    public function getIdleTimeout()
    {
        return $this->idleTimeout;
    }

    /**
     * @return mixed|null
     */
    private function consume(ConsumerContainer $container, AMQPMessage $message)
    {
        $event = new ConsumerEvent($message, $container);
        $this->dispatchEvent($event, ConsumerEvents::PRE_CONSUME);

        $result = $this->invoke($container, $message);

        $this->dispatchEvent($event, ConsumerEvents::POST_CONSUME);

        return $result;
    }

    /**
     * @return mixed|null
     *
     * @throws ConsumerContainerException
     */
    private function invoke(ConsumerContainer $consumerContainer, AMQPMessage $message)
    {
        $payload = $this->serializer->deserialize($message->body, $consumerContainer->getMessageClass(), 'json');

        try {
            $result = $consumerContainer->invoke($payload);
        } catch (Throwable $e) {
            $containerException = new ConsumerContainerException($consumerContainer, $message, $payload, $e);
            if ($this->errorHandlers->isEmpty()) {
                throw $containerException;
            }

            $this->errorHandlers->map(static function (ErrorHandlerInterface $handler) use ($containerException) {
                $handler->handle($containerException);
            });

            return null;
        }

        return $result;
    }

    private function dispatchEvent(Event $event, string $eventName): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event, $eventName);
    }
}
