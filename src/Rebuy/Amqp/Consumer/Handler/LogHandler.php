<?php

namespace Rebuy\Amqp\Consumer\Handler;

use Psr\Log\LoggerInterface;
use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;

class LogHandler implements ErrorHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(ConsumerContainerException $ex)
    {
        $messageClass = $ex->getConsumerContainer()->getMessageClass();
        $message = sprintf('Exception [%s] occurred while processing message [%s]', $ex->getMessage(), $messageClass);

        $this->logger->warning($message, ['exception' => $ex]);
    }
}
