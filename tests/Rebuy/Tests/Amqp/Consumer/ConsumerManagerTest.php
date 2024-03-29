<?php

namespace Rebuy\Tests\Amqp\Consumer;

use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Amqp\Consumer\Annotation\Parser;
use Rebuy\Amqp\Consumer\ConsumerManager;
use Rebuy\Amqp\Consumer\Exception\ConsumerException;
use Rebuy\Amqp\Consumer\Serializer\Serializer;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumerManagerTest extends TestCase
{
    use ProphecyTrait;

    const EXCHANGE_NAME = 'exchange';

    /**
     * @var ConsumerManager
     */
    private $manager;

    /**
     * @var AMQPChannel|ObjectProphecy
     */
    private $channel;

    /**
     * @var Serializer|ObjectProphecy
     */
    private $serializer;

    /**
     * @var ObjectProphecy|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ObjectProphecy|Parser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->channel = $this->prophesize(AMQPChannel::class);
        $this->serializer = $this->prophesize(Serializer::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->parser = $this->prophesize(Parser::class);

        $this->manager = new ConsumerManager(
            $this->channel->reveal(),
            self::EXCHANGE_NAME,
            $this->serializer->reveal(),
            $this->parser->reveal()
        );
        $this->manager->setEventDispatcher($this->eventDispatcher->reveal());
    }

    /**
     * @test
     */
    public function register_consumer_with_string_parameter_should_throw_exception()
    {
        $this->expectException(ConsumerException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "string" given');

        $this->manager->registerConsumer('string');
    }

    /**
     * @test
     */
    public function register_consumer_with_int_parameter_should_throw_exception()
    {
        $this->expectException(ConsumerException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "integer" given');

        $this->manager->registerConsumer(12);
    }

    /**
     * @test
     */
    public function register_consumer_should_declare_queue()
    {
        $consumer = new stdClass();
        $container = $this->prophesize(ConsumerContainer::class);
        $container->getConsumerName()->willReturn('myName');
        $container->getBindings()->willReturn([]);
        $container->getPrefetchCount()->willReturn(1);

        $this->parser->getConsumerMethods($consumer)->willReturn([$container]);

        $this->manager->registerConsumer($consumer);

        $this->channel->queue_declare('myName', false, true, false, false)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function register_consumer_with_same_name_should_throw_exception()
    {
        $this->expectException(ConsumerException::class);

        $container = $this->prophesize(ConsumerContainer::class);
        $container->getConsumerName()->willReturn('myName');
        $container->getMethodName()->willReturn('MyConsumer::method');
        $container->getBindings()->willReturn([]);
        $container->getPrefetchCount()->willReturn(1);

        $container2 = $this->prophesize(ConsumerContainer::class);
        $container2->getConsumerName()->willReturn('myName');
        $container2->getMethodName()->willReturn('OtherConsumer::method');


        $consumer = new stdClass();
        $this->parser->getConsumerMethods($consumer)->willReturn([$container->reveal(), $container2->reveal()]);

        $this->manager->registerConsumer($consumer);
    }

    /**
     * @test
     */
    public function register_consumer_should_bind_queues()
    {
        $consumer = new stdClass();
        $binding2 = 'binding2';
        $binding1 = 'binding1';
        $consumerName = 'myName';
        $bindings = [$binding1, $binding2];
        $containerMock = $this->prophesize(ConsumerContainer::class);
        $containerMock->getBindings()->willReturn($bindings);
        $containerMock->getConsumerName()->willReturn($consumerName);
        $containerMock->getPrefetchCount()->willReturn(1);

        $this->parser->getConsumerMethods($consumer)->willReturn([$containerMock]);

        $this->channel->basic_qos(null, 1, false)->shouldBeCalled();
        $this->channel->basic_consume($consumerName, Argument::any(), Argument::any(), Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->channel->queue_declare($consumerName, Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->channel->queue_bind($consumerName, self::EXCHANGE_NAME, $binding1)->shouldBeCalled();
        $this->channel->queue_bind($consumerName, self::EXCHANGE_NAME, $binding2)->shouldBeCalled();

        $this->manager->registerConsumer($consumer);
    }
}
