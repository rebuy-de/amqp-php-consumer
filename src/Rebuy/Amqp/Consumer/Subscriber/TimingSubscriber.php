<?php

namespace Rebuy\Amqp\Consumer\Subscriber;

use League\StatsD\Client;
use Rebuy\Amqp\Consumer\ConsumerEvent;
use Rebuy\Amqp\Consumer\ConsumerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class TimingSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Client $statsdClient, private readonly Stopwatch $stopwatch)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsumerEvents::POST_CONSUME => 'postConsume',
            ConsumerEvents::PRE_CONSUME => 'preConsume',
        ];
    }

    public function preConsume(ConsumerEvent $args): void
    {
        $eventName = $this->getEventName($args);

        $this->stopwatch->start($eventName);
    }

    public function postConsume(ConsumerEvent $args): void
    {
        $consumerName = $args->getConsumerContainer()->getConsumerName();
        $event = $this->stopwatch->stop($this->getEventName($args));

        $this->statsdClient->timing($consumerName, $event->getDuration());
    }

    private function getEventName(ConsumerEvent $args): string
    {
        $name = $args->getConsumerContainer()->getConsumerName();
        $tag = $args->getMessage()->getDeliveryTag();

        return sprintf('%s-%s', $name, $tag);
    }
}
