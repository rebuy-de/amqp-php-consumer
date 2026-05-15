<?php

namespace Rebuy\Tests\Amqp\Consumer\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rebuy\Amqp\Consumer\Annotation\Parser;
use Rebuy\Tests\Amqp\Consumer\Stubs\Consumer;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithAttributes;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithInvalidAnnotation;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithInvalidParameter;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithPrefetchCount;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithTwoParameters;
use Rebuy\Tests\Amqp\Consumer\Stubs\Message;

class ParserTest extends TestCase
{
    #[Test]
    public function parser_should_parse_valid_configuration(): void
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new Consumer();

        $consumerMethods = $parser->getConsumerMethods($consumer);

        $consumerMethod = $consumerMethods[0];

        verify($consumerMethods)->arrayCount(1);
        verify($consumerMethod->getBindings())->arrayContains('genericMessage');
        verify($consumerMethod->getMessageClass())->equals(Message::class);
    }

    #[Test]
    public function parser_should_use_default_prefetch_count(): void
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new Consumer();

        $consumerMethods = $parser->getConsumerMethods($consumer);
        $consumerMethod = $consumerMethods[0];

        verify($consumerMethod->getPrefetchCount())->equals(1);
    }

    #[Test]
    public function parser_should_use_prefetch_count_from_annotation(): void
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithPrefetchCount();

        $consumerMethods = $parser->getConsumerMethods($consumer);
        $consumerMethod = $consumerMethods[0];

        verify($consumerMethod->getPrefetchCount())->equals(100);
    }

    #[Test]
    public function parser_should_support_attributes(): void
    {
        $parser = new Parser(new AnnotationReader(), 'prefix');
        $consumer = new ConsumerWithAttributes();

        $consumerMethods = $parser->getConsumerMethods($consumer);
        $consumerMethod = $consumerMethods[0];

        verify($consumerMethod->getConsumerName())->equals('prefix-consume-with-attributes');
        verify($consumerMethod->getPrefetchCount())->equals(100);
    }

    #[Test]
    public function parser_should_not_parse_consumer_method_with_two_parameters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithTwoParameters();

        $parser->getConsumerMethods($consumer);
    }

    #[Test]
    public function parser_should_not_parse_consumer_method_without_marker_interface(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithInvalidParameter();

        $parser->getConsumerMethods($consumer);
    }

    #[Test]
    public function parser_should_throw_exception_when_name_for_consumer_is_not_set(): void
    {
        $this->expectException(AnnotationException::class);

        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithInvalidAnnotation();

        $parser->getConsumerMethods($consumer);
    }
}
