<?php

use Illuminate\Container\Container;
use PhpDev\Facades\Configuration;
use PhpDev\Facades\PhpFpm;
use PhpDev\Facades\Nginx;
use PhpDev\Facades\Site;
use Silly\Application;
use Silly\Command\Command;

use function PhpDev\info;
use function PhpDev\output;
use function PhpDev\warning;

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
    Configuration::install();
    PhpFpm::install();
    output();
    Nginx::install();

    info(PHP_EOL . 'PhpDev installed successfully!');
})->descriptions('Install the PhpDev services');

/**
 * Stop the PhpDev services.
 */
$app->command('start', function () {
    // start php fpm
    PhpFpm::start();
    output();
    // start nginx
    Nginx::start();

    info(PHP_EOL . 'All PhpDev services have been started.');
})->descriptions('start PhpDev services');

/**
 * Stop the PhpDev services.
 */
$app->command('stop', function () {
    // stop nginx
    Nginx::stop();
    output();
    // stop php fpm
    PhpFpm::stop();

    info(PHP_EOL . 'All PhpDev services have been stopped.');
})->descriptions('Stop PhpDev services');

$app->command('link [path] [-s|--site=] [-p|--php=]', function ($path, $site, $php) {
    $site = Site::name($site);
    $php = PhpFpm::getVersion($php);
    if (! PhpFpm::installed($php)) {
        output('Error:');
        warning(sprintf('PHP %s not installed yet', $php));
        // end command
        return Command::FAILURE;
    }
    echo $php;
})->descriptions('Link the current working directory to Phpdev', [
    'path' => 'Root directory path for the site. Default: current directory path',
    '--site' => 'Site name. Default: current directory name',
    '--php' => 'Which php version to use. Default: current php version'
]);

return $app;
