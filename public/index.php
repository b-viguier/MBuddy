<?php

use Bveing\MBuddy\App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $envPrefix = isset($context['IOS_VERSION']) ? 'ipad_' : '';
    
    return new Kernel($envPrefix.$context['APP_ENV'], (bool) $context['APP_DEBUG']);
};