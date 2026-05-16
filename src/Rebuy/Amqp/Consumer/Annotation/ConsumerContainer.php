<?php

namespace Rebuy\Amqp\Consumer\Annotation;

use Rebuy\Amqp\Consumer\Message\MessageInterface;
use ReflectionMethod;

class ConsumerContainer
{
    public function __construct(
        private readonly string $prefix,
        private readonly object $obj,
        private readonly ReflectionMethod $method,
        private readonly Consumer $attribute,
    ) {
    }

    /**
     * @return string[]
     */
    public function getBindings(): array
    {
        if (1 != $this->method->getNumberOfParameters()) {
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

    public function getConsumerIdentification(): string
    {
        return sprintf('%s-%s', $this->getConsumerName(), $this->getRoutingKey());
    }

    public function getRoutingKey(): ?string
    {
        $class = $this->method->getParameters()[0]->getType()?->getName();
        if (!is_a($class, MessageInterface::class, true)) {
            return null;
        }

        return $class::getRoutingKey();
    }

    public function getMessageClass(): ?string
    {
        return $this->method->getParameters()[0]->getType()?->getName();
    }

    public function getConsumerName(): string
    {
        return sprintf('%s-%s', $this->prefix, $this->attribute->name);
    }

    public function getPrefetchCount(): int
    {
        return $this->attribute->prefetchCount;
    }

    public function invoke($payload): mixed
    {
        return $this->method->invoke($this->obj, $payload);
    }

    public function getMethodName(): string
    {
        $className = $this->method->getDeclaringClass()->getName();
        $methodName = $this->method->getName();

        return sprintf('%s::%s', $className, $methodName);
    }
}
