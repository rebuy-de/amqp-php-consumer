<?php

namespace Rebuy\Amqp\Consumer\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Consumer
{
    const DEFAULT_PREFETCH_COUNT = 1;

    /**
     * @Required
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $prefetchCount = self::DEFAULT_PREFETCH_COUNT;
}
