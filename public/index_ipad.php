<?php

use Bveing\MBuddy\App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel('ipad', true);
};