<?php

namespace Rebuy\Tests\Amqp\Consumer\Annotation;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Rebuy\Amqp\Consumer\Annotation\Consumer as ConsumerAnnotation;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Tests\Amqp\Consumer\Stubs\Consumer;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithInvalidParameter;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithTwoParameters;
use Rebuy\Tests\Amqp\Consumer\Stubs\Message;
use ReflectionMethod;

class ConsumerContainerTest extends TestCase
{
    use ProphecyTrait;

    const TEST_PREFIX = "test";

    /**
     * @test
     */
    public function invoke_should_invoke_reflection()
    {
        $payload = new Message();
        $consumer = new Consumer();

        $reflectionMethod = $this->prophesize(ReflectionMethod::class);
        $reflectionMethod->invoke($consumer, $payload)->shouldBeCalled();

        $container = new ConsumerContainer(
            self::TEST_PREFIX,
            $consumer,
            $reflectionMethod->reveal(),
            new ConsumerAnnotation()
        );
        $container->invoke($payload);
    }

    /**
     * @test
     */
    public function get_bindings_should_return_empty_array_if_interface_is_not_implemented()
    {
        $consumer = new ConsumerWithInvalidParameter();
        $method = new ReflectionMethod($consumer, 'classWithoutImplementingInterface');

        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation());
        $result = $container->getBindings();

        verify($result)->empty();
    }

    /**
     * @test
     */
    public function get_bindings_should_return_empty_array_if_parameter_count_is_not_exactly_one()
    {
        $consumer = new ConsumerWithTwoParameters();
        $method = new ReflectionMethod($consumer, 'consume');

        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation());
        $result = $container->getBindings();

        verify($result)->empty();
    }

    /**
     * @test
     */
    public function get_bindings_should_return_empty_array_parameter_is_not_a_class()
    {
        $consumer = new ConsumerWithInvalidParameter();
        $method = new ReflectionMethod($consumer, 'consume');

        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation());
        $result = $container->getBindings();

        verify($result)->empty();
    }

    /**
     * @test
     */
    public function get_bindings_should_return_array_with_two_bindings()
    {
        $consumer = new Consumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation());
        $result = $container->getBindings();

        verify($result)->notEmpty();
        verify($result)->arrayCount(2);
    }

    /**
     * @test
     */
    public function get_bindings_should_return_correct_bindings()
    {
        $consumer = new Consumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $consumerAnnotation = new ConsumerAnnotation();
        $consumerAnnotation->name = "consume-method";
        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, $consumerAnnotation);
        $result = $container->getBindings();

        verify($result)->arrayContains("test-consume-method-genericMessage");
        verify($result)->arrayContains("genericMessage");
    }

    /**
     * @test
     */
    public function get_consumer_name_should_return_correct_name()
    {
        $consumer = new Consumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $consumerAnnotation = new ConsumerAnnotation();
        $consumerAnnotation->name = "consume-method";
        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, $consumerAnnotation);
        $result = $container->getConsumerName();

        verify($result)->equals(self::TEST_PREFIX . '-' . $consumerAnnotation->name);
    }

    /**
     * @test
     */
    public function get_method_name_should_return_class_with_method_name()
    {
        $consumer = new Consumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $consumerAnnotation = new ConsumerAnnotation();
        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, $consumerAnnotation);

        $result = $container->getMethodName();
        verify($result)->equals(Consumer::class . '::consume');
    }
}
