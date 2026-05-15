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
     * @param string $prefix
     */
    public function __construct(Reader $reader, $prefix = '')
    {
        $this->reader = $reader;
        $this->prefix = $prefix;
    }

    /**
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
     * @throws InvalidArgumentException
     */
    private function validateMethod(ReflectionMethod $method): void
    {
        if (1 != $method->getNumberOfParameters()) {
            throw new InvalidArgumentException('A @Consumer is only allowed to have exactly one parameter: ' . $method);
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
            if (Consumer::class === $attribute->getName()) {
                return $attribute->newInstance();
            }
        }

        return $this->reader->getMethodAnnotation($method, Consumer::class);
    }
}
