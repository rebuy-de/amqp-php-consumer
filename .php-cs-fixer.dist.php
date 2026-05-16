<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return new Config()
    ->setRiskyAllowed(true)
    ->setRules([
        '@auto' => true,
        '@auto:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'php_unit_method_casing' => false,
        'phpdoc_align' => ['align' => 'left'],
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => false,
        'native_function_invocation' => false,
    ])
    ->setFinder(
        new Finder()
            ->in(
                [
                    __DIR__ . '/src',
                    __DIR__ . '/tests',
                ]
            )
    );
