<?php

namespace Rebuy\Tests\Amqp\Consumer\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Rebuy\Amqp\Consumer\Annotation\Consumer as ConsumerAnnotation;
use Rebuy\Amqp\Consumer\Annotation\Parser;
use Rebuy\Tests\Amqp\Consumer\Stubs\Consumer;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithInvalidAnnotation;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithInvalidParameter;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithPrefetchCount;
use Rebuy\Tests\Amqp\Consumer\Stubs\ConsumerWithTwoParameters;
use Rebuy\Tests\Amqp\Consumer\Stubs\Message;

class ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function parser_should_parse_valid_configuration()
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new Consumer();

        $consumerMethods = $parser->getConsumerMethods($consumer);

        $consumerMethod = $consumerMethods[0];

        verify($consumerMethods)->count(1);
        verify($consumerMethod->getBindings())->contains('genericMessage');
        verify($consumerMethod->getMessageClass())->equals(Message::class);
    }

    /**
     * @tests
     */
    public function parser_should_use_default_prefetch_count()
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new Consumer();

        $consumerMethods = $parser->getConsumerMethods($consumer);
        $consumerMethod = $consumerMethods[0];

        verify($consumerMethod->getPrefetchCount())->equals(ConsumerAnnotation::DEFAULT_PREFETCH_COUNT);
    }

    /**
     * @tests
     */
    public function parser_should_use_prefetch_count_from_annotation()
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithPrefetchCount();

        $consumerMethods = $parser->getConsumerMethods($consumer);
        $consumerMethod = $consumerMethods[0];

        verify($consumerMethod->getPrefetchCount())->equals(100);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function parser_should_not_parse_consumer_method_with_two_parameters()
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithTwoParameters();

        $parser->getConsumerMethods($consumer);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function parser_should_not_parse_consumer_method_without_marker_interface()
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithInvalidParameter();

        $parser->getConsumerMethods($consumer);
    }

    /**
     * @test
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     */
    public function parser_should_throw_exception_when_name_for_consumer_is_not_set()
    {
        $parser = new Parser(new AnnotationReader());
        $consumer = new ConsumerWithInvalidAnnotation();

        $parser->getConsumerMethods($consumer);
    }
}
