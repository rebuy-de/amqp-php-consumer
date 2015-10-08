Error Handlers
==============

You can register several error handlers which will be called when an exception in the consuming process is thrown.
Every error handler must implement the interface ``Rebuy\Amqp\Consumer\Handler\ErrorHandlerInterface``, this
interface only requires one method ``handle(ConsumerContainerException $ex)``.

An error handler can be registered in the following way::

    $manager = new Rebuy\Amqp\Consumer\ConsumerManager(...);
    $manager->registerErrorHandler(new MyErrorHandler());


.. danger::
    As soon as one error handler is registered, the consuming of the message is considered successful. If you want
    to stop the consuming process, you must throw the passed exception (or an own exception) by yourself.

Implemented error handlers
--------------------------

Currently there are two error handlers implemented in this library:

- *RequeuerHandler*: Requeues the message so it can be processed at a later time
- *LoggerHandler*: Uses a `LoggerInterface`_ to log a warning message (this handler is only useful in combination
  with the *RequeuerHandler*)

.. _LoggerInterface: https://github.com/php-fig/log
