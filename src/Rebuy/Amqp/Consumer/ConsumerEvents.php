<?php

namespace Rebuy\Amqp\Consumer;

final class ConsumerEvents
{
    public const string PRE_CONSUME = 'rebuy.consumer.preConsume';
    public const string POST_CONSUME = 'rebuy.consumer.postConsume';
}
