<?php

namespace Rebuy\Tests\Amqp\Consumer\Handler;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Amqp\Consumer\Attribute\ConsumerContainer;
use Rebuy\Amqp\Consumer\ClientInterface;
use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;
use Rebuy\Amqp\Consumer\Handler\RequeuerHandler;
use Rebuy\Tests\Amqp\Consumer\Stubs\Message;

class RequeuerHandlerTest extends TestCase
{
    use ProphecyTrait;

    public const string CONSUMER_IDENTIFICATION = 'my-consumer-identification';

    /**
     * @var ObjectProphecy|ConsumerContainer
     */
    private $consumerContainer;

    /**
     * @var ObjectProphecy|ClientInterface
     */
    private $client;

    private RequeuerHandler $handler;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(ClientInterface::class);
        $this->handler = new RequeuerHandler($this->client->reveal());

        $this->consumerContainer = $this->prophesize(ConsumerContainer::class);
        $this->consumerContainer->getConsumerIdentification()->willReturn(self::CONSUMER_IDENTIFICATION);
    }

    #[Test]
    public function handle_should_send_message_with_routing_header(): void
    {
        $exceptionMessage = 'Fatal error';
        $exception = new Exception($exceptionMessage);
        $payloadMessage = new Message();
        $amqpMessage = $this->prophesize(AMQPMessage::class);

        $amqpMessage->has('application_headers')->willReturn(false);
        $amqpMessage->set('application_headers', Argument::that(static function (AMQPTable $table) {
            verify($table->getNativeData())->arrayHasKey('routing');

            return $table;
        }))->shouldBeCalled();

        $this->client->sendMessage($amqpMessage->reveal(), self::CONSUMER_IDENTIFICATION)->shouldBeCalled();

        $exception = new ConsumerContainerException(
            $this->consumerContainer->reveal(),
            $amqpMessage->reveal(),
            $payloadMessage,
            $exception
        );

        $this->handler->handle($exception);
    }

    #[Test]
    public function handle_should_send_message_with_type_header(): void
    {
        $exceptionMessage = 'Fatal error';
        $exception = new Exception($exceptionMessage);
        $payloadMessage = new Message();
        $amqpMessage = $this->prophesize(AMQPMessage::class);

        $amqpMessage->has('application_headers')->willReturn(false);
        $amqpMessage->set('application_headers', Argument::that(static function (AMQPTable $table) {
            verify($table->getNativeData())->arrayHasKey('type');

            return $table;
        }))->shouldBeCalled();

        $this->client->sendMessage($amqpMessage->reveal(), self::CONSUMER_IDENTIFICATION)->shouldBeCalled();

        $exception = new ConsumerContainerException(
            $this->consumerContainer->reveal(),
            $amqpMessage->reveal(),
            $payloadMessage,
            $exception
        );

        $this->handler->handle($exception);
    }

    #[Test]
    public function handle_should_use_existing_headers(): void
    {
        $table = new AMQPTable();
        $exceptionMessage = 'Fatal error';
        $exception = new Exception($exceptionMessage);
        $payloadMessage = new Message();
        $amqpMessage = $this->prophesize(AMQPMessage::class);

        $amqpMessage->has('application_headers')->willReturn(true);
        $amqpMessage->get('application_headers')->willReturn($table);
        $amqpMessage->set('application_headers', $table)->shouldBeCalled();

        $this->client->sendMessage($amqpMessage->reveal(), self::CONSUMER_IDENTIFICATION)->shouldBeCalled();

        $exception = new ConsumerContainerException(
            $this->consumerContainer->reveal(),
            $amqpMessage->reveal(),
            $payloadMessage,
            $exception
        );

        $this->handler->handle($exception);
    }
}
