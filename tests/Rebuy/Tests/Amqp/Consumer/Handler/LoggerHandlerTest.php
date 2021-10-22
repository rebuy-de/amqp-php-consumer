<?php

namespace Rebuy\Tests\Amqp\Consumer\Handler;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;
use Rebuy\Amqp\Consumer\Handler\LogHandler;
use Rebuy\Amqp\Consumer\Message\MessageInterface;

class LoggerHandlerTest extends TestCase
{
    use ProphecyTrait;

    const MESSAGE_CLASS = 'MyClass';

    /**
     * @var LogHandler
     */
    private $handler;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ConsumerContainer|ObjectProphecy
     */
    private $consumerContainer;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->handler = new LogHandler($this->logger->reveal());
        $this->consumerContainer = $this->prophesize(ConsumerContainer::class);
        $this->consumerContainer->getMessageClass()->willReturn(self::MESSAGE_CLASS);
    }

    /**
     * @test
     */
    public function handle_should_log_message_with_payload()
    {
        $exceptionMessage = "Fatal error";
        $baseException = new Exception($exceptionMessage);
        $payloadMessage = $this->prophesize(MessageInterface::class);

        $exception = new ConsumerContainerException(
            $this->consumerContainer->reveal(),
            new AMQPMessage(),
            $payloadMessage->reveal(),
            $baseException
        );

        $this->handler->handle($exception);

        $this->logger->warning(
            Argument::allOf(
                Argument::containingString(self::MESSAGE_CLASS),
                Argument::containingString($exceptionMessage)
            ),
            Argument::that(function ($context) use ($baseException) {
                verify($context['exception'])->instanceOf(ConsumerContainerException::class);
                verify($context['exception']->getPrevious())->equals($baseException);

                return $context;
            })
        )->shouldHaveBeenCalled();
    }
}
