<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

$loader = require $autoloadFile;
$loader->add('Rebuy\Tests', __DIR__);

AnnotationRegistry::registerLoader([$loader, 'loadClass']);
