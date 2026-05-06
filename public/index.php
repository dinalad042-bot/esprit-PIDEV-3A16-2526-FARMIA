<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Force le fuseau horaire ici pour correspondre à MySQL (+00:00)
date_default_timezone_set('UTC'); 

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};