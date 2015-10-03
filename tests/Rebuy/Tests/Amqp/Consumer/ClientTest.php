<?php

namespace Rebuy\Tests\Amqp\Consumer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Amqp\Consumer\Client;
use Rebuy\Tests\Amqp\Consumer\Stubs\Message;

class ClientTest extends PHPUnit_Framework_TestCase
{
    const EXCHANGE_NAME = 'rebuy';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ObjectProphecy|Serializer
     */
    private $serializer;

    /**
     * @var ObjectProphecy|AMQPChannel
     */
    private $channel;

    protected function setUp()
    {
        $this->channel = $this->prophesize(AMQPChannel::class);
        $this->serializer = $this->prophesize(Serializer::class);

        $this->client = new Client($this->channel->reveal(), self::EXCHANGE_NAME, $this->serializer->reveal());
    }

    /**
     * @test
     */
    public function sendMessage_should_send_amqp_message()
    {
        $message = new AMQPMessage();

        $routingKey = 'routingKey';

        $this->channel->basic_publish($message, self::EXCHANGE_NAME, $routingKey)->shouldBeCalled();

        $this->client->sendMessage($message, $routingKey);
    }

    /**
     * @test
     */
    public function sendMessage_should_create_and_send_new_amqp_message()
    {
        $routingKey = 'routingKey';

        $this->channel->basic_publish(Argument::type(AMQPMessage::class), self::EXCHANGE_NAME, $routingKey)->shouldBeCalled();

        $message = new Message();
        $this->serializer->serialize($message, 'json', Argument::type(SerializationContext::class))->shouldBeCalled();

        $this->client->sendMessage($message, $routingKey);
    }

    /**
     * @test
     */
    public function sendMessage_should_use_routing_key_from_message()
    {
        $message = new Message();
        $routingKey = $message->getName();

        $this->channel->basic_publish(Argument::type(AMQPMessage::class), self::EXCHANGE_NAME, $routingKey)->shouldBeCalled();
        $this->serializer->serialize($message, 'json', Argument::type(SerializationContext::class))->shouldBeCalled();

        $this->client->sendMessage($message);
    }
}
