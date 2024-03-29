<?php

namespace Rebuy\Amqp\Consumer\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Rebuy\Amqp\Consumer\Message\MessageInterface;
use ReflectionMethod;

class ConsumerContainer
{
    /**
     * @var ReflectionMethod
     */
    private $method;

    /**
     * @var Annotation
     */
    private $annotation;

    /**
     * @var object
     */
    private $obj;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param string $prefix
     * @param object $obj
     * @param ReflectionMethod $method
     * @param Consumer $annotation
     */
    public function __construct($prefix, $obj, ReflectionMethod $method, Consumer $annotation)
    {
        $this->obj = $obj;
        $this->method = $method;
        $this->annotation = $annotation;
        $this->prefix = $prefix;
    }

    /**
     * @return string[]
     */
    public function getBindings()
    {
        if ($this->method->getNumberOfParameters() != 1) {
            return [];
        }

        $class = $this->method->getParameters()[0]->getType()?->getName();
        if (null === $class) {
            return [];
        }

        if (!is_a($class, MessageInterface::class, true)) {
            return [];
        }

        return [$this->getConsumerIdentification(), $this->getRoutingKey()];
    }

    /**
     * @return string
     */
    public function getConsumerIdentification()
    {
        return sprintf('%s-%s', $this->getConsumerName(), $this->getRoutingKey());
    }

    /**
     * @return mixed
     */
    public function getRoutingKey()
    {
        $class = $this->method->getParameters()[0]->getType()?->getName();
        if (!is_a($class, MessageInterface::class, true)) {
            return null;
        }

        return $class::getRoutingKey();
    }

    /**
     * @return string
     */
    public function getMessageClass()
    {
        return $this->method->getParameters()[0]->getType()?->getName();
    }

    /**
     * @return string
     */
    public function getConsumerName()
    {
        return sprintf('%s-%s', $this->prefix, $this->annotation->name);
    }

    /**
     * @return int
     */
    public function getPrefetchCount()
    {
        return $this->annotation->prefetchCount;
    }

    /**
     * @param mixed $payload
     *
     * @return mixed
     */
    public function invoke($payload)
    {
        return $this->method->invoke($this->obj, $payload);
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        $className = $this->method->getDeclaringClass()->getName();
        $methodName = $this->method->getName();

        return sprintf('%s::%s', $className, $methodName);
    }
}
