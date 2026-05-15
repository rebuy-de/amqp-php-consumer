<?php

namespace Rebuy\Amqp\Consumer\Handler;

use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;

interface ErrorHandlerInterface
{
    public function handle(ConsumerContainerException $ex);
}
