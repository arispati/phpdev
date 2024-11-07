<?php

use Arispati\Phpdev\Commands;
use Silly\Application;

// Load correct autoloader depending on install location.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} else {
    require getenv('HOME') . '/.composer/vendor/autoload.php';
}

$version = '1.0.0';

$app = new Application('Arispati Vel', $version);

$app->command('hello', new Commands\Hello());

return $app;
