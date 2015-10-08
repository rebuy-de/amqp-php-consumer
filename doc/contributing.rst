Contributing
============

We are happy for contributions. Before you invest a lot of time however, best
open an issue on github to discuss your idea. Then we can coordinate efforts
if somebody is already working on the same thing.

Testing the Library
-------------------

This chapter describes how to run the tests that are included with this library.

First clone the repository, install the vendors, then run the tests:

.. code-block:: bash

    $ git clone https://github.com/rebuy-de/amqp-php-consumer.git
    $ cd amqp-php-consumer
    $ composer install --dev
    $ bin/phpunit

Building the Documentation
--------------------------

First `install Sphinx`_, then build the docs:

.. code-block:: bash

    $ cd doc
    $ make html

.. _install Sphinx: http://sphinx-doc.org/latest/install.html
