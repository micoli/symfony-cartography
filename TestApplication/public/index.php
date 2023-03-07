<?php

declare(strict_types=1);

use App\Kernel;

$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'project_dir' => dirname(__DIR__),
];

require_once dirname(__DIR__) . '/../vendor/autoload_runtime.php';

return fn (array $context) => new Kernel((string) $context['APP_ENV'], (bool) $context['APP_DEBUG']);
