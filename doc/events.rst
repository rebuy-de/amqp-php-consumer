Events
======

There are currently two events dispatched when consuming a message:

- ``Rebuy\Amqp\Consumer\ManagerEvents::PRE_CONSUME``: Before the message is consumed
- ``Rebuy\Amqp\Consumer\ManagerEvents::POST_CONSUME``: After the message has been consumed

These events are dispatched by an symfony2 event dispatcher. If you want to listen to one of these events, you have
to create a subsriber/listener, add it to the event dispatcher and set the dispatcher to the manager::

    $dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();
    $dispatcher->addListener(Rebuy\Amqp\Consumer\ManagerEvents::PRE_CONSUME, $myListener);
    $dispatcher->addSubscriber(new MySubscriber());

    $manager = new Rebuy\Amqp\Consumer\ConsumerManager(...);
    $manager->setEventDispatcher($dispatcher);

Implemented Subscriber
----------------------

Some useful subscribers are already shipped with this library:

- *TimingSubscriber*: Uses `symfony/stopwatch`_ and `league/statsd`_ for writing timing metrics to statds
- *LogSubscriber*: Uses a `LoggerInterface`_ to log a debug message for every consumed message

.. _symfony/stopwatch: https://github.com/symfony/stopwatch
.. _league/statsd: https://github.com/thephpleague/statsd
.. _LoggerInterface: https://github.com/php-fig/log
