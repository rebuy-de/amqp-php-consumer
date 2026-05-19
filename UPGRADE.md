# Upgrade to 2.0.0

## Dropped annotation support 

This version drops support for [doctrine/annotations](https://github.com/doctrine/annotations). Annotations should be replaced with attributes

Before:

```php
use Rebuy\Amqp\Consumer\Annotation\Consumer;

class YourConsumer
{
    /**
     * @Consumer(name="consumer-name")
     */
    public function consume(Message $message): void
    {
    }
}
```

After:

```php
use Rebuy\Amqp\Consumer\Attribute\Consumer;

class YourConsumer
{
    #[Consumer(name: 'consumer-name')]
    public function consume(Message $message): void
    {
    }
}

```

## Renamed `Annotation` namespace to `Attribute`

* Renamed `Rebuy\Amqp\Consumer\Annotation\Consumer` to `Rebuy\Amqp\Consumer\Attribute\Consumer` 
* Renamed `Rebuy\Amqp\Consumer\Annotation\ConsumerContainer` to `Rebuy\Amqp\Consumer\Attribute\ConsumerContainer`
* Renamed `Rebuy\Amqp\Consumer\Annotation\Parser` to `Rebuy\Amqp\Consumer\Attribute\Parser`
