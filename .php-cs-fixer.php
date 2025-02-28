<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in([
        sprintf('%s/config', __DIR__),
        sprintf('%s/src', __DIR__),
        sprintf('%s/tests', __DIR__),
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
    ])
    ->setFinder($finder)
    ->setParallelConfig(ParallelConfigFactory::detect());
