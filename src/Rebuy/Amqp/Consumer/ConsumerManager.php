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
    const DEFAULT_IDLE_TIMEOUT = 900;

    private array $consumerContainers;

    private Serializer $serializer;

    private EventDispatcherInterface $eventDispatcher;

    private AMQPChannel $channel;

    private string $exchangeName;

    private ArrayCollection $errorHandlers;

    private int $idleTimeout = self::DEFAULT_IDLE_TIMEOUT;

    private Parser $parser;

    /**
     * @param AMQPChannel $channel
     * @param string $exchangeName
     * @param Serializer $serializer
     * @param Parser $parser
     */
    public function __construct(AMQPChannel $channel, string $exchangeName, Serializer $serializer, Parser $parser)
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
    public function registerConsumer(object $consumer): void
    {
        $type = gettype($consumer);
        if ($type !== 'object') {
            throw new ConsumerException(sprintf('Expected argument of type "object", "%s" given', $type));
        }

        $consumerContainers = $this->parser->getConsumerMethods($consumer);
        foreach ($consumerContainers as $consumerContainer) {
            $this->registerConsumerContainer($consumerContainer);
        }
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ConsumerContainer $consumerContainer
     *
     * @throws ConsumerException
     */
    private function registerConsumerContainer(ConsumerContainer $consumerContainer): void
    {
        $consumerName = $consumerContainer->getConsumerName();
        if (isset($this->consumerContainers[$consumerName])) {
            $currentConsumer = $this->consumerContainers[$consumerName];
            throw new ConsumerException(
                sprintf(
                    'Can not register consumer method [%s] because the consumer method [%s] already uses that name',
                    $consumerContainer->getMethodName(),
                    $currentConsumer->getMethodName()
                )
            );
        }

        $this->channel->queue_declare($consumerName, false, true, false, false, arguments: ['x-queue-type' => 'quorum']);
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

    /**
     * @param ErrorHandlerInterface $errorHandler
     */
    public function registerErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandlers->add($errorHandler);
    }

    public function setIdleTimeout(int $idleTimeout): void
    {
        $this->idleTimeout = $idleTimeout;
    }

    /**
     * @return int
     */
    public function getIdleTimeout(): int
    {
        return $this->idleTimeout;
    }

    /**
     * @param Annotation\ConsumerContainer $container
     * @param AMQPMessage $message
     *
     * @return mixed|null
     */
    private function consume(ConsumerContainer $container, AMQPMessage $message): mixed
    {
        $event = new ConsumerEvent($message, $container);
        $this->dispatchEvent($event, ConsumerEvents::PRE_CONSUME);

        $result = $this->invoke($container, $message);

        $this->dispatchEvent($event, ConsumerEvents::POST_CONSUME);

        return $result;
    }

    /**
     * @param ConsumerContainer $consumerContainer
     * @param AMQPMessage $message
     *
     * @throws ConsumerContainerException
     * @return mixed|null
     */
    private function invoke(ConsumerContainer $consumerContainer, AMQPMessage $message): mixed
    {
        $payload = $this->serializer->deserialize($message->getBody(), $consumerContainer->getMessageClass(), 'json');

        try {
            $result = $consumerContainer->invoke($payload);
        } catch (Throwable $e) {
            $containerException = new ConsumerContainerException($consumerContainer, $message, $payload, $e);
            if ($this->errorHandlers->isEmpty()) {
                throw $containerException;
            }

            $this->errorHandlers->map(function (ErrorHandlerInterface $handler) use ($containerException) {
                $handler->handle($containerException);
            });

            return null;
        }

        return $result;
    }

    private function dispatchEvent(Event $event, string $eventName)
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event, $eventName);
    }
}
