<?php

use Arispati\Phpdev\App\Command;
use Silly\Application;

// Load correct autoloader depending on install location.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} else {
    require getenv('HOME') . '/.composer/vendor/autoload.php';
}

// Phpdev version
$version = '1.0.0';

// Initiate application
$app = new Application('Arispati Phpdev', $version);

// command classes
$command = new Command();

// register commands
$app->command('link [path] [-s|--site=] [-p|--php=]', [$command, 'link'])
    ->descriptions('Link the current working directory to Phpdev', [
        'path' => 'Root directory path for the site. Default: current directory path',
        '--site' => 'Site name. Default: current directory name',
        '--php' => 'Which php version to use. Default: current php version'
    ]);

return $app;
