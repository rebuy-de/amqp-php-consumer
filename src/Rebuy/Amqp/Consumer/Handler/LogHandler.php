<?php

namespace Rebuy\Amqp\Consumer\Handler;

use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;

class LogHandler implements ErrorHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function handle(ConsumerContainerException $ex): void
    {
        $messageClass = $ex->getConsumerContainer()->getMessageClass();
        $message = sprintf('Exception [%s] occurred while processing message [%s]', $ex->getMessage(), $messageClass);

        $this->logger->warning($message, ['exception' => $ex]);
    }
}
