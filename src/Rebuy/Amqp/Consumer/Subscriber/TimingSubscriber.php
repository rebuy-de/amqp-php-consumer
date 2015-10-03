<?php

namespace Rebuy\Amqp\Consumer\Subscriber;

use League\StatsD\Client;
use Rebuy\Amqp\Consumer\ConsumeEvent;
use Rebuy\Amqp\Consumer\ManagerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class TimingSubscriber implements EventSubscriberInterface
{
    /**
     * @var Client
     */
    private $statsdClient;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @param Client $statsdClient
     * @param Stopwatch $stopwatch
     */
    public function __construct(Client $statsdClient, Stopwatch $stopwatch)
    {
        $this->statsdClient = $statsdClient;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ManagerEvents::POST_CONSUME => 'postConsume',
            ManagerEvents::PRE_CONSUME => 'preConsume',
        ];
    }

    /**
     * @param ConsumeEvent $args
     */
    public function preConsume(ConsumeEvent $args)
    {
        $eventName = $this->getEventName($args);

        $this->stopwatch->start($eventName);
    }

    /**
     * @param ConsumeEvent $args
     */
    public function postConsume(ConsumeEvent $args)
    {
        $consumerName = $args->getConsumerContainer()->getConsumerName();
        $event = $this->stopwatch->stop($this->getEventName($args));

        $this->statsdClient->timing($consumerName, $event->getDuration());
    }

    /**
     * @param ConsumeEvent $args
     *
     * @return string
     */
    private function getEventName(ConsumeEvent $args)
    {
        $name = $args->getConsumerContainer()->getConsumerName();
        $tag = $args->getMessage()->delivery_info['delivery_tag'];

        return sprintf('%s-%s', $name, $tag);
    }
}
