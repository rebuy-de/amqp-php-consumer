<?php

namespace Rebuy\Amqp\Consumer\Handler;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Rebuy\Amqp\Consumer\ClientInterface;
use Rebuy\Amqp\Consumer\Exception\ConsumerContainerException;

class RequeuerHandler implements ErrorHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle(ConsumerContainerException $ex)
    {
        $genericMessage = $ex->getPayloadMessage();
        $routingKey = $ex->getConsumerContainer()->getConsumerIdentification();

        $message = $ex->getAmqpMessage();
        $table = $this->getHeaders($message);
        $nativeData = $table->getNativeData();

        if (!isset($nativeData['type'])) {
            $table->set('type', $genericMessage->getName(), AMQPTable::T_STRING_LONG);
        }

        if (!isset($nativeData['routing'])) {
            $table->set('routing', $routingKey, AMQPTable::T_STRING_LONG);
        }

        $message->set('application_headers', $table);

        $this->client->sendMessage($message, $routingKey);
    }

    /**
     * @param AMQPMessage $message
     *
     * @return AMQPTable
     */
    private function getHeaders(AMQPMessage $message)
    {
        if ($message->has('application_headers')) {
            return $message->get('application_headers');
        }

        return new AMQPTable();
    }
}
