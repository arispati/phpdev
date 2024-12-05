<?php

use Illuminate\Container\Container;
use PhpDev\Facade\Config;
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

$app->command('link [path] [-s|--site=] [-p|--php=]', function ($path, $site, $php) {
    $config = Config::read();
    $site = Site::name($site);
    // validate site
    if (isset($config['sites'][$site])) {
        Helper::write('Site name already linked');
        return Command::FAILURE;
    }
    // PHP FPM
    $php = PhpFpm::getVersion($php);
    // validate php version installed
    if (! PhpFpm::installed($php)) {
        Helper::write('Error:');
        Helper::warning(sprintf('PHP %s not installed yet', $php));
        return Command::FAILURE;
    }
    // validate php configuration
    if (! in_array($php, $config['php'])) {
        PhpFpm::createConfigurationFiles($php);
        // add php version to configuration
        $phpConfigs = $config['php'];
        $phpConfigs[] = $php;
        // desc sort
        ksort($phpConfigs);
        // update config
        Config::updateKey('php', $phpConfigs);
    }
    print_r($php);

    Helper::info(PHP_EOL . sprintf('%s successfully linked', $site));
})->descriptions('Link the current working directory to Phpdev', [
    'path' => 'Root directory path for the site. Default: current directory path',
    '--site' => 'Site name. Default: current directory name',
    '--php' => 'Which php version to use. Default: current php version'
]);

/**
 * Run the application.
 */
$app->run();
