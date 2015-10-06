<?php

namespace Rebuy\Amqp\Consumer\Subscriber;

use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\ConsumerEvent;
use Rebuy\Amqp\Consumer\ConsumerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsumerEvents::PRE_CONSUME => 'preConsume',
        ];
    }

    /**
     * @param ConsumerEvent $event
     */
    public function preConsume(ConsumerEvent $event)
    {
        $container = $event->getConsumerContainer();
        $message = sprintf(
            "Processing message [%s] for consumer [%s] with payload [%s]",
            $container->getMessageName(),
            $container->getMethodName(),
            $event->getMessage()->body
        );

        $this->logger->debug($message);
    }
}

