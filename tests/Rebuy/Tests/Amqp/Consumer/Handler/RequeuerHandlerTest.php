<?php

namespace Rebuy\Tests\Amqp\Consumer\Handler;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\Client;
use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;
use Rebuy\Amqp\Consumer\Handler\RequeuerHandler;
use Rebuy\Tests\Amqp\Consumer\Stubs\Message;

class RequeuerHandlerTest extends \PHPUnit_Framework_TestCase
{
    const CONSUMER_IDENTIFICATION = 'my-consumer-identification';

    /**
     * @var ObjectProphecy|ConsumerContainer
     */
    private $consumerContainer;

    /**
     * @var ObjectProphecy|Client
     */
    private $client;

    /**
     * @var RequeuerHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->client = $this->prophesize(Client::class);
        $this->handler = new RequeuerHandler($this->client->reveal());

        $this->consumerContainer = $this->prophesize(ConsumerContainer::class);
        $this->consumerContainer->getConsumerIdentification()->willReturn(self::CONSUMER_IDENTIFICATION);
    }

    /**
     * @test
     */
    public function handle_should_send_message_with_routing_header()
    {
        $exceptionMessage = "Fatal error";
        $exception = new Exception($exceptionMessage);
        $payloadMessage = new Message();
        $amqpMessage = $this->prophesize(AMQPMessage::class);

        $amqpMessage->has('application_headers')->willReturn(false);
        $amqpMessage->set('application_headers', Argument::that(function (AMQPTable $table) {
            verify($table->getNativeData())->hasKey('routing');

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

    /**
     * @test
     */
    public function handle_should_send_message_with_type_header()
    {
        $exceptionMessage = "Fatal error";
        $exception = new Exception($exceptionMessage);
        $payloadMessage = new Message();
        $amqpMessage = $this->prophesize(AMQPMessage::class);

        $amqpMessage->has('application_headers')->willReturn(false);
        $amqpMessage->set('application_headers', Argument::that(function (AMQPTable $table) {
            verify($table->getNativeData())->hasKey('type');

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

    /**
     * @test
     */
    public function handle_should_use_existing_headers()
    {
        $table = new AMQPTable();
        $exceptionMessage = "Fatal error";
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
