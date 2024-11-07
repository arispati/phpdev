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

$app = new Application('Arispati Phpdev', $version);

// command classes
$nginxCommand = new Commands\Nginx();
$phpCommand = new Commands\Php();

// register commands
$app->command('nginx action', [$nginxCommand, 'handle']);
$app->command('php action', [$phpCommand, 'handle']);

return $app;
