<?php

namespace Rebuy\Amqp\Consumer\Annotation;

use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;
use Rebuy\Amqp\Consumer\Message\MessageInterface;
use ReflectionClass;
use ReflectionMethod;

class Parser
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param Reader $reader
     * @param string $prefix
     */
    public function __construct(Reader $reader, $prefix = '')
    {
        $this->reader = $reader;
        $this->prefix = $prefix;
    }

    /**
     * @param $obj
     * @return ConsumerContainer[]
     */
    public function getConsumerMethods($obj)
    {
        $class = new ReflectionClass($obj);
        $consumerMethods = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $annotation = $this->getConsumerAnnotationOrAttribute($method);
            if (null === $annotation) {
                continue;
            }

            $this->validateMethod($method);

            $consumerMethods[] = new ConsumerContainer($this->prefix, $obj, $method, $annotation);
        }

        return $consumerMethods;
    }

    /**
     * @param ReflectionMethod $method
     *
     * @throws InvalidArgumentException
     */
    private function validateMethod(ReflectionMethod $method)
    {
        if ($method->getNumberOfParameters() != 1) {
            throw new InvalidArgumentException("A @Consumer is only allowed to have exactly one parameter: " . $method);
        }

        $parameter = $method->getParameters()[0];
        $class = $parameter->getType()?->getName();
        if (!is_a($class, MessageInterface::class, true)) {
            throw new InvalidArgumentException('A @Consumer\'s parameter must implement ' . MessageInterface::class);
        }
    }

    private function getConsumerAnnotationOrAttribute(ReflectionMethod $method): ?Consumer
    {
        $reflectionAttributes = $method->getAttributes();
        foreach ($reflectionAttributes as $attribute) {
            if ($attribute->getName() === Consumer::class) {
                return $attribute->newInstance();
            }
        }

        return $this->reader->getMethodAnnotation($method, Consumer::class);

    }
}
