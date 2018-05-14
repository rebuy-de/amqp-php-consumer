Create and Configure the ConsumerManager
=========================================

The consumer manager is responsible for registering a consumer and starting the consuming process.

Create AMQP Connection
----------------------

You need to create an AMQP connection with an AMQP channel which will then be used by the comsuner manager::

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
The easiest way to do so is by using the ``SerializerBuilder`` from the JMS library::

    use Rebuy\Amqp\Consumer\Serializer\JMSSerializerAdapter;
    use JMS\Serializer\SerializerBuilder;

    $serializer = SerializerBuilder::create()->build();
    $serializerAdapter = new JMSSerializerAdapter($serializer);

If you'd rather want to use the symfony serializer, do the following::

    use Rebuy\Amqp\Consumer\Serializer\SymfonySerializerAdapter;
    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());

    $serializer = new Serializer($normalizers, $encoders);
    $serializerAdapter = new SymfonySerializerAdapter($serializer);

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

We have now everything we need to create the consumer manager, register consumers and start the consuming process::

    $manager = new Rebuy\Amqp\Consumer\ConsumerManager($channel, $exchangeName, $serializerAdapter, $parser);
    $manager->registerConsumer(new MyConsumer());

    $manager->wait()

.. caution::
    The consuming process might stop under the following conditions:

    - An exception in one of the consumers is thrown
    - No message has been processed in the last 900 seconds (this value can be altered with the method ``ConsumerManager#setIdleTimeout``)

.. note::
    The ``wait`` method is a blocking process. This method waits for new messages and passes every message to
    its desired consumer (if one exists).

