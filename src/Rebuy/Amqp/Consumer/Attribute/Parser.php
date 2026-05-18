<?php

namespace Rebuy\Amqp\Consumer\Attribute;

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
            $attribute = $this->getConsumerAttribute($method);
            if (null === $attribute) {
                continue;
            }

            $consumerMethods[] = new ConsumerContainer($this->prefix, $obj, $method, $attribute);
        }

        return $consumerMethods;
    }

    private function getConsumerAttribute(ReflectionMethod $method): ?Consumer
    {
        $reflectionAttributes = $method->getAttributes(Consumer::class);
        foreach ($reflectionAttributes as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
