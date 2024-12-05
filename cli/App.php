<?php

use Illuminate\Container\Container;
use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Facade\Site;
use PhpDev\Helper\Helper;
use Silly\Application;
use Silly\Command\Command;

// Load correct autoloader depending on install location.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    // get from global composer vendor
    require getenv('HOME') . '/.config/composer/vendor/autoload.php';
}

// Create container
Container::setInstance(new Container());

// Version
$version = '0.1.0';

// Create application
$app = new Application('PhpDev', $version);

/**
 * Register commands
 */

// Start the PhpDev services.
$app->command('start', function () {
    Helper::info(PHP_EOL . 'Starting PhpDev services');
    Helper::write();
    // start php fpm
    PhpFpm::start();
    Helper::write();
    // start nginx
    Nginx::start();

    Helper::info(PHP_EOL . 'All PhpDev services have been started.');
})->descriptions('start PhpDev services');

// Stop the PhpDev services.
$app->command('stop', function () {
    Helper::info(PHP_EOL . 'Stopping PhpDev services');
    Helper::write();
    // stop nginx
    Nginx::stop();
    Helper::write();
    // stop php fpm
    PhpFpm::stop();

    Helper::info(PHP_EOL . 'PhpDev services have been stopped.');
})->descriptions('Stop PhpDev services');

// Restart the PhpDev services.
$app->command('restart', function () {
    Helper::info(PHP_EOL . 'Restart PhpDev service');
    Helper::write();
    // restart PHP FPM
    PhpFpm::restart();
    Helper::write();
    // restart nginx
    Nginx::restart();

    Helper::info(PHP_EOL . 'PhpDev services has been restarted');
})->descriptions('Restart PhpDev services');

// Link site to PhpDev
$app->command('link [path] [-s|--site=] [-p|--php=]', function ($path, $site, $php) {
    // define variable
    $path = Site::path($path);
    $config = Config::read();
    $site = Site::name($site);
    // validate site
    if (isset($config['sites'][$site])) {
        Helper::write(PHP_EOL . 'Site name already linked');
        // exit command
        return Command::FAILURE;
    }
    // PHP FPM
    $php = PhpFpm::getVersion($php);
    // validate php version installed
    if (! PhpFpm::installed($php)) {
        Helper::warning(PHP_EOL . 'Error:');
        Helper::write(sprintf('PHP %s not installed yet', $php));
        // exit command
        return Command::FAILURE;
    }
    Helper::info(sprintf(PHP_EOL . 'Linking %s', $site));
    Helper::write();
    // create nginx configuration
    Nginx::createConfiguration($site, $path, $php);
    // restart nginx
    Nginx::restart();
    // validate php configuration
    if (! in_array($php, $config['php'])) {
        Helper::write();
        PhpFpm::createConfigurationFiles($php);
        // start php fpm
        PhpFpm::start($php);
    }
    // add site to config
    Config::addSite('link', $site, $path, $php);

    Helper::info(PHP_EOL . sprintf('%s has been linked', $site));
})->descriptions('Link the current working directory to PhpDev', [
    'path' => 'Root directory path for the site. Default: current directory path',
    '--site' => 'Site name. Default: current directory name',
    '--php' => 'Which php version to use. Default: current php version'
]);

// Show linked site
$app->command('links', function () {
    $headers = ['name', 'type', 'php', 'path'];
    $sites = array_map(function ($item) use ($headers) {
        $result = [];
        foreach ($headers as $header) {
            $result[] = isset($item[$header]) ? $item[$header] : '-';
        }
        return $result;
    }, Config::read('sites'));
    // sort
    usort($sites, function ($a, $b) {
        return $a[3] <=> $b[3];
    });
    // show table
    Helper::table($headers, array_values($sites));
})->descriptions('Show all linked sites');

// Unlink site
$app->command('unlink site', function ($site) {
    // define variable
    $site = Site::name($site);
    $configSites = Config::read('sites');
    // validate site
    if (! isset($configSites[$site])) {
        Helper::write('Site name is not linked yet');
        return Command::FAILURE;
    }
    Helper::info(sprintf('Unlinking %s', $site));
    Helper::write();
    // remove site config
    Nginx::removeConfiguration($site);
    // restart nginx
    Nginx::restart();
    // remove site config
    Config::removeSite($site);
    // synch PHP FPM
    if ($unused = Config::synchPhp()) {
        Helper::info(PHP_EOL . 'There is an unused PHP FPM');
        // new line
        PhpFpm::stop($unused);
    }

    Helper::info(PHP_EOL . sprintf('%s successfully unlinked', $site));
})->descriptions('Unlink site');
