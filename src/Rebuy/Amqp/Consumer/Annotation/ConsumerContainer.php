<?php

namespace Rebuy\Amqp\Consumer\Annotation;

use InvalidArgumentException;
use Rebuy\Amqp\Consumer\Message\MessageInterface;
use ReflectionMethod;
use ReflectionNamedType;

class ConsumerContainer
{
    /**
     * @var class-string<MessageInterface>
     */
    private readonly string $messageClass;

    public function __construct(
        private readonly string $prefix,
        private readonly object $obj,
        private readonly ReflectionMethod $method,
        private readonly Consumer $attribute,
    ) {
        $this->messageClass = $this->validateAndGetMessageClass();
    }

    /**
     * @return string[]
     */
    public function getBindings(): array
    {
        return [$this->getConsumerIdentification(), $this->getRoutingKey()];
    }

    public function getConsumerIdentification(): string
    {
        return sprintf('%s-%s', $this->getConsumerName(), $this->getRoutingKey());
    }

    public function getRoutingKey(): string
    {
        return ($this->messageClass)::getRoutingKey();
    }

    /**
     * @return class-string<MessageInterface>
     */
    public function getMessageClass(): string
    {
        return $this->messageClass;
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

    /**
     * @return class-string<MessageInterface>
     *
     * @throws InvalidArgumentException
     */
    private function validateAndGetMessageClass(): string
    {
        if (1 != $this->method->getNumberOfParameters()) {
            throw new InvalidArgumentException('A @Consumer is only allowed to have exactly one parameter: ' . $this->method);
        }

        $type = $this->method->getParameters()[0]->getType();
        $class = $type instanceof ReflectionNamedType ? $type->getName() : null;
        if (null === $class || !is_a($class, MessageInterface::class, true)) {
            throw new InvalidArgumentException('A @Consumer\'s parameter must implement ' . MessageInterface::class);
        }

        return $class;
    }
}
