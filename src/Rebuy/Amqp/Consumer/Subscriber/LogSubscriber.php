<?php

namespace Rebuy\Amqp\Consumer\Subscriber;

use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\ConsumerEvent;
use Rebuy\Amqp\Consumer\ConsumerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsumerEvents::PRE_CONSUME => 'preConsume',
        ];
    }

    public function preConsume(ConsumerEvent $event): void
    {
        $container = $event->getConsumerContainer();
        $message = sprintf(
            'Processing message [%s] for consumer [%s] with payload [%s]',
            $container->getRoutingKey(),
            $container->getMethodName(),
            $event->getMessage()->body
        );

        $this->logger->debug($message);
    }
}
