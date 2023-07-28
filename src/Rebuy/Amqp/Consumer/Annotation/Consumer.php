<?php

namespace Rebuy\Amqp\Consumer\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Consumer
{
    private const DEFAULT_PREFETCH_COUNT = 1;

    public function __construct(
        public string $name,
        public int $prefetchCount = self::DEFAULT_PREFETCH_COUNT
    ) {
    }
}
