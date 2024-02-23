<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/bin',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/web',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'none',
            'closure_fn_spacing' => 'none',
        ],
        'ordered_imports' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
