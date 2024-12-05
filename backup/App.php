<?php

use Illuminate\Container\Container;
use PhpDevBackup\Facades\Configuration;
use PhpDevBackup\Facades\PhpFpm;
use PhpDevBackup\Facades\Nginx;
use PhpDevBackup\Facades\Site;
use Silly\Application;
use Silly\Command\Command;

use function PhpDevBackup\info;
use function PhpDevBackup\output;
use function PhpDevBackup\table;
use function PhpDevBackup\warning;

// Load correct autoloader depending on install location.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    // get from global composer vendor
    require getenv('HOME') . '/.config/composer/vendor/autoload.php';
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
    $path = rtrim(sprintf('%s/%s', PHPDEV_CURRENT_DIR_PATH, $path), '/');
    $config = Configuration::read();
    $site = Site::name($site);
    // validate site
    if (isset($config['sites'][$site])) {
        output('Site name already linked');
        return Command::FAILURE;
    }
    $php = PhpFpm::getVersion($php);
    // validate selected php version
    if (! PhpFpm::installed($php)) {
        output('Error:');
        warning(sprintf('PHP %s not installed yet', $php));
        // end command
        return Command::FAILURE;
    }
    // validate php config
    if (! in_array($php, $config['php'])) {
        PhpFpm::createConfigurationFiles($php);
        PhpFpm::start($php);
        // update php config
        $config['php'][] = $php;
        Configuration::updateKey('php', $config['php']);
    }
    // create nginx configuration
    Nginx::createConfiguration($site, $path, $php);
    // restart nginx
    Nginx::restart();
    // update site config
    $config['sites'][$site] = [
        'name' => $site,
        'path' => $path,
        'php' => $php
    ];
    Configuration::updateKey('sites', $config['sites']);

    info(PHP_EOL . sprintf('%s successfully linked', $site));
})->descriptions('Link the current working directory to Phpdev', [
    'path' => 'Root directory path for the site. Default: current directory path',
    '--site' => 'Site name. Default: current directory name',
    '--php' => 'Which php version to use. Default: current php version'
]);

$app->command('links', function () {
    $sites = Configuration::read('sites');
    table(['name', 'path', 'php'], array_values($sites));
    // print_r($sites);
})->descriptions('Show all linked sites');

$app->command('unlink [site]', function ($site) {
    // validate empty site
    if (empty($site)) {
        output('Invalid site name');
        return Command::FAILURE;
    }
    $site = Site::name($site);
    $configSites = Configuration::read('sites');
    // validate site
    if (! isset($configSites[$site])) {
        output('Site name is not linked yet');
        return Command::FAILURE;
    }
    // remove configuration
    Nginx::removeConfiguration($site);
    // restart nginx
    Nginx::restart();
    // remove config
    unset($configSites[$site]);
    // update config
    Configuration::updateKey('sites', $configSites);

    info(PHP_EOL . sprintf('%s successfully unlinked', $site));
})->descriptions('Unlink site');

return $app;
