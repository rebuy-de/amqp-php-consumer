<?php

namespace Rebuy\Tests\Amqp\Consumer\Subscriber;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\ConsumerEvent;
use Rebuy\Amqp\Consumer\Subscriber\LogSubscriber;

class LogSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    private LogSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->subscriber = new LogSubscriber($this->logger->reveal());
    }

    #[Test]
    public function preConsume_should_log_debug_message(): void
    {
        $body = 'payload-body';
        $messageName = 'message-name';
        $methodName = 'MyClass::myMethod';

        /** @var ConsumerContainer $consumerContainer */
        $consumerContainer = $this->prophesize(ConsumerContainer::class);
        $consumerContainer->getRoutingKey()->willReturn($messageName);
        $consumerContainer->getMethodName()->willReturn($methodName);

        $event = new ConsumerEvent(new AMQPMessage($body), $consumerContainer->reveal());
        $this->subscriber->preConsume($event);

        $this->logger->debug(Argument::allOf(
            Argument::containingString($messageName),
            Argument::containingString($methodName),
            Argument::containingString($body)
        ))->shouldHaveBeenCalled();
    }
}
