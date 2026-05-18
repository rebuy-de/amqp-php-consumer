<?php

namespace Rebuy\Tests\Amqp\Consumer\Annotation;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Rebuy\Amqp\Consumer\Annotation\Consumer as ConsumerAnnotation;
use Rebuy\Amqp\Consumer\Annotation\ConsumerContainer;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithInvalidParameter;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithTwoParameters;
use Rebuy\Tests\Amqp\Consumer\Stubs\Message;
use Rebuy\Tests\Amqp\Consumer\Stubs\SimpleConsumer;
use ReflectionMethod;

class ConsumerContainerTest extends TestCase
{
    use ProphecyTrait;

    public const string TEST_PREFIX = 'test';

    #[Test]
    public function invoke_should_invoke_reflection(): void
    {
        $consumer = new SimpleConsumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation('name'));
        $container->invoke(new Message());

        verify($consumer->invocationCount)->equals(1);
    }

    #[Test]
    public function constructor_should_throw_when_parameter_does_not_implement_message_interface(): void
    {
        $consumer = new ConsumerWithInvalidParameter();
        $method = new ReflectionMethod($consumer, 'classWithoutImplementingInterface');

        $this->expectException(InvalidArgumentException::class);
        new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation('name'));
    }

    #[Test]
    public function constructor_should_throw_when_method_has_more_than_one_parameter(): void
    {
        $consumer = new ConsumerWithTwoParameters();
        $method = new ReflectionMethod($consumer, 'consume');

        $this->expectException(InvalidArgumentException::class);
        new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation('name'));
    }

    #[Test]
    public function constructor_should_throw_when_parameter_is_not_a_class(): void
    {
        $consumer = new ConsumerWithInvalidParameter();
        $method = new ReflectionMethod($consumer, 'consume');

        $this->expectException(InvalidArgumentException::class);
        new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation('name'));
    }

    #[Test]
    public function get_bindings_should_return_array_with_two_bindings(): void
    {
        $consumer = new SimpleConsumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, new ConsumerAnnotation('name'));
        $result = $container->getBindings();

        verify($result)->notEmpty();
        verify($result)->arrayCount(2);
    }

    #[Test]
    public function get_bindings_should_return_correct_bindings(): void
    {
        $consumer = new SimpleConsumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $consumerAnnotation = new ConsumerAnnotation('name');
        $consumerAnnotation->name = 'consume-method';
        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, $consumerAnnotation);
        $result = $container->getBindings();

        verify($result)->arrayContains('test-consume-method-genericMessage');
        verify($result)->arrayContains('genericMessage');
    }

    #[Test]
    public function get_consumer_name_should_return_correct_name(): void
    {
        $consumer = new SimpleConsumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $consumerAnnotation = new ConsumerAnnotation('name');
        $consumerAnnotation->name = 'consume-method';
        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, $consumerAnnotation);
        $result = $container->getConsumerName();

        verify($result)->equals(self::TEST_PREFIX . '-' . $consumerAnnotation->name);
    }

    #[Test]
    public function get_method_name_should_return_class_with_method_name(): void
    {
        $consumer = new SimpleConsumer();
        $method = new ReflectionMethod($consumer, 'consume');

        $consumerAnnotation = new ConsumerAnnotation('name');
        $container = new ConsumerContainer(self::TEST_PREFIX, $consumer, $method, $consumerAnnotation);

        $result = $container->getMethodName();
        verify($result)->equals(SimpleConsumer::class . '::consume');
    }
}
