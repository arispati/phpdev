<?php

use Phpdev\App\Facades\Command;
use Phpdev\App\Facades\Configuration;
use Phpdev\App\Facades\PhpFpm;
use Illuminate\Container\Container;
use Silly\Application;

// Load correct autoloader depending on install location.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} else {
    require getenv('HOME') . '/.composer/vendor/autoload.php';
}

// create container
Container::setInstance(new Container());

// Phpdev version
$version = '1.0.0';

// Initiate application
$app = new Application('PhpDev', $version);

// register commands
$app->command('install', function () {
    // Configuration::install();
    // PhpFpm::install();
    echo 'test';
})->descriptions('Install the PhpDev services');

// $app->command('link [path] [-s|--site=] [-p|--php=]', function ($path, $site, $php) {
//     Command::link($path, $site, $php);
// })->descriptions('Link the current working directory to Phpdev', [
//     'path' => 'Root directory path for the site. Default: current directory path',
//     '--site' => 'Site name. Default: current directory name',
//     '--php' => 'Which php version to use. Default: current php version'
// ]);

return $app;
