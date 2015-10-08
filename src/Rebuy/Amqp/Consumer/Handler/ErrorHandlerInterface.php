<?php

namespace Rebuy\Amqp\Consumer\Handler;

use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;

interface ErrorHandlerInterface
{
    /**
     * @param ConsumerContainerException $ex
     */
    public function handle(ConsumerContainerException $ex);
}
