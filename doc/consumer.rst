Creating Consumers
==================

Let's assume you have an amqp message which will be published when an order has been created. This message has
the routing key ``order-created`` with the payload ``{"order_id": $SOME_ID}``. In this example we create a consumer
which sends an email to the customer when this message will be published.

First of all, you have to create a PHP class which represents this message::

    namespace My\Consumer;

    use JMS\Serializer\Annotation\Type;
    use Rebuy\Amqp\Consumer\Message\MessageInterface;

    class OrderCreatedMessage implements MessageInterface
    {
        /**
         * @Type("integer")
         *
         * @var int
         */
        public $orderId;

        public static function getRoutingKey()
        {
            return 'order-created';
        }
    }

.. note::
    Since this library uses the `jms/serializer`_ component to deserialize the payload for all messages, we have
    to define a ``@Type`` for the property ``$orderId``.

With this message we are able to create our consumer which will send an email to the customer::

    class OrderConsumer
    {
        private $orderService;
        private $emailService;

        public function __construct($orderService, $emailService)
        {
            $this->orderService = $orderService;
            $this->emailService = $emailService;
        }

        /**
         * @Consumer(name="order-created-send-email")
         */
        public function sendMail(OrderCreatedMessage $message)
        {
            $order = $this->orderService->loadOrder($message->orderId);
            $this->emailService->sendOrderCreatedEmail($order);
        }
    }

.. note::
    You can create multiple consumers which consume the same message, but they must use a different name, otherwise
    an ``ConsumerException`` is thrown.


Now that you have created a consumer, you can go on to the next section and create the
:doc:`consumer manager <consumer-manager>`


.. _jms/serializer: http://jmsyst.com/libs/serializer
