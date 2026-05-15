<?php

namespace Rebuy\Amqp\Consumer;

final class ConsumerEvents
{
    public const PRE_CONSUME = 'rebuy.consumer.preConsume';
    public const POST_CONSUME = 'rebuy.consumer.postConsume';
}
