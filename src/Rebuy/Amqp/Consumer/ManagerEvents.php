<?php

namespace Rebuy\Amqp\Consumer;

final class ManagerEvents
{
    const PRE_CONSUME = 'rebuy.consumer.preConsume';
    const POST_CONSUME = 'rebuy.consumer.postConsume';
}
