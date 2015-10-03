Create and Configure the Consumer Manager
=========================================

The consumer manager is responsible for registering a consumer and starting the consuming process.

Create AMQP Connection
----------------------

You need to create an AMQP connection with an AMQP channel which will then be used by the manager::

    $connection = new PhpAmqpLib\Connection\AMQPSocketConnection('localhost', 5672, 'username', 'password');
    $channel = $connection->channel();

    $passive = false;
    $durable = true;
    $autoDelete = false;
    $type = 'topic';

    $channel->exchange_declare('your-exchange-name', $type, $passive, $durable, $autoDelete);

If you need other values than the ones defined, feel free to adjust them, but it is necessary to declare the exchange
before you can go on.

Create a JMS Serializer
-----------------------

In order to deserialize the payload of an AMQP message we have to create a Serializer object
The easiest way to do so is by using the ``SerializerBuilder`` from this library::

    $serializer = JMS\Serializer\SerializerBuilder::create()->build();

Create the Annotation Parser
----------------------------

The annotation parser is responsible for parsing all the consumer annotations and creating a ConsumerContainer.
The container is an abstraction of the consumer method and holds all information which are necessary to consume
the message::

    $reader = new Doctrine\Common\Annotations\AnnotationReader();
    $parser = new Rebuy\Amqp\Consumer\Annotation\Parser($reader);

.. tip::

    You can also use a FileCacheReader instead of the AnnotationReader. Example:
    ``$reader = new FileCacheReader(new AnnotationReader(), '/path/to/cache');``

Tying it all together
---------------------

We have now everything we need to create the manager, register consumers and start the consuming process::

    $manager = new Rebuy\Amqp\Consumer\Manager($channel, $exchangeName, $serializer, $parser);
    $manager->registerConsumer(new MyConsumer());

    $manager->wait()

.. caution::
    The consuming process might stop under the following conditions:

    - An exception in one of the consumers is thrown
    - No message has been processed in the last 900 seconds (this value can be altered with the method ``Manager#setIdleTimeout``)

.. note::
    The ``wait`` method is a blocking process. This method waits for new messages and passes every message to
    its desired consumer (if one exists).

