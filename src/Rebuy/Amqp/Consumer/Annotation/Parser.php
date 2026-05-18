<?php

namespace Rebuy\Amqp\Consumer\Annotation;

use ReflectionClass;
use ReflectionMethod;

class Parser
{
    public function __construct(private readonly string $prefix = '')
    {
    }

    /**
     * @return ConsumerContainer[]
     */
    public function getConsumerMethods($obj): array
    {
        $class = new ReflectionClass($obj);
        $consumerMethods = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $annotation = $this->getConsumerAnnotationOrAttribute($method);
            if (null === $annotation) {
                continue;
            }

            $consumerMethods[] = new ConsumerContainer($this->prefix, $obj, $method, $annotation);
        }

        return $consumerMethods;
    }

    private function getConsumerAnnotationOrAttribute(ReflectionMethod $method): ?Consumer
    {
        $reflectionAttributes = $method->getAttributes(Consumer::class);
        foreach ($reflectionAttributes as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
