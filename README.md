AMQP PHP Consumer
============

Introduction
------------

This library allows you to define consumers for AMQP with [doctrine annotations](https://github.com/doctrine/annotations).
For consuming messages the AMQP-Library [videlalvaro/php-amqplib](https://github.com/videlalvaro/php-amqplib) is used.

Features
--------

* Define consumers based on annotations
* Deserialize AMQP messages with the [jms/serializer](http://jmsyst.com/libs/serializer)
* Register error handlers
* Register pre and post consume events

Documentation
-------------

For more information, see [the documentation](doc/index.rst).

License
-------

This library is released under the MIT license. See the included
[LICENSE](LICENSE) file for more information.
