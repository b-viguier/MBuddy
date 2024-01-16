<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/bin',
        __DIR__.'/src',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'none',
            'closure_fn_spacing' => 'none',
        ],

    ])
    ->setFinder($finder);
