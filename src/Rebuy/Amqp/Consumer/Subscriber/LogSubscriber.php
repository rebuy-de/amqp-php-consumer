<?php

namespace Rebuy\Amqp\Consumer\Subscriber;

use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\ConsumeEvent;
use Rebuy\Amqp\Consumer\ManagerEvents;
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
            ManagerEvents::PRE_CONSUME => 'preConsume',
        ];
    }

    /**
     * @param ConsumeEvent $event
     */
    public function preConsume(ConsumeEvent $event)
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

