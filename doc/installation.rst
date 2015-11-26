Getting started
===============

Installation
------------

The amqp-php-consumer library is available on Packagist_. You can install it using
Composer_:

.. code-block:: bash

    $ composer require rebuy/amqp-php-consumer

.. note::

    This library follows `Semantic Versioning`_.  Except for major versions, we
    aim to not introduce BC breaks in new releases. You should still test your
    application after upgrading though. What is a bug fix for somebody could
    break something for others when they where (probably unawares) relying on
    that bug.

Configuration
-------------

There are two things you need to do to get started:

1. :doc:`create one ore more consumer <consumer>`
2. :doc:`create a consumer manager <consumer-manager>`

.. _Packagist: https://packagist.org/packages/rebuy/amqp-php-consumer
.. _Composer: http://getcomposer.org
.. _Semantic Versioning: http://semver.org/
