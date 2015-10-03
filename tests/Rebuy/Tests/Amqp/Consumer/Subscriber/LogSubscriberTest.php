<?php

namespace Rebuy\Tests\Amqp\Consumer\Subscriber;

use PhpAmqpLib\Message\AMQPMessage;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\ConsumeEvent;
use Rebuy\Amqp\Consumer\Subscriber\LogSubscriber;

class LogSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var LogSubscriber
     */
    private $subscriber;

    protected function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->subscriber = new LogSubscriber($this->logger->reveal());
    }

    /**
     * @test
     */
    public function preConsume_should_log_debug_message()
    {
        $body = 'payload-body';
        $messageName = 'message-name';
        $methodName = 'MyClass::myMethod';

        $consumerContainer = $this->prophesize(ConsumerContainer::class);
        $consumerContainer->getMessageName()->willReturn($messageName);
        $consumerContainer->getMethodName()->willReturn($methodName);

        $event = new ConsumeEvent(new AMQPMessage($body), $consumerContainer->reveal());
        $this->subscriber->preConsume($event);

        $this->logger->debug(Argument::allOf(
            Argument::containingString($messageName),
            Argument::containingString($methodName),
            Argument::containingString($body)
        ))->shouldHaveBeenCalled();
    }
}
