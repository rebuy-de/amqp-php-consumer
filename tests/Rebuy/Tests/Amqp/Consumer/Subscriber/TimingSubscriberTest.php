<?php

namespace Rebuy\Tests\Amqp\Consumer\Subscriber;

use League\StatsD\Client;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\ConsumerEvent;
use Rebuy\Amqp\Consumer\Subscriber\TimingSubscriber;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class TimingSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var Stopwatch|ObjectProphecy
     */
    private $stopwatch;

    /**
     * @var Client|ObjectProphecy
     */
    private $statsdClient;

    /**
     * @var TimingSubscriber
     */
    private $subscriber;

    protected function setUp(): void
    {
        $this->statsdClient = $this->prophesize(Client::class);
        $this->stopwatch = $this->prophesize(Stopwatch::class);

        $this->subscriber = new TimingSubscriber($this->statsdClient->reveal(), $this->stopwatch->reveal());
    }

    /**
     * @test
     */
    public function preConsume_with_postConsume_should_add_timing_entry()
    {
        $timing = 2342;
        $deliveryTag = 1;
        $consumerName = 'consumer';
        $eventName = $consumerName . '-' . $deliveryTag;
        $message = new AMQPMessage('body');
        $message->delivery_info['delivery_tag'] = $deliveryTag;

        $container = $this->prophesize(ConsumerContainer::class);
        $container->getConsumerName()->willReturn($consumerName);
        $args = new ConsumerEvent($message, $container->reveal());

        $event = $this->prophesize(StopwatchEvent::class);
        $event->getDuration()->willReturn($timing);
        $this->stopwatch->start($eventName)->shouldBeCalled();
        $this->stopwatch->stop($eventName)->willReturn($event->reveal());

        $this->subscriber->preConsume($args);
        $this->subscriber->postConsume($args);

        $this->statsdClient->timing($consumerName, $timing)->shouldHaveBeenCalled();
    }
}
