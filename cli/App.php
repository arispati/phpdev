<?php

use PhpDev\Commands\InstallCommand;
use PhpDev\Commands\LinkCommand;
use PhpDev\Commands\LinksCommand;
use PhpDev\Commands\ProxyCommand;
use PhpDev\Commands\RestartCommand;
use PhpDev\Commands\SslCommand;
use PhpDev\Commands\SslRemoveCommand;
use PhpDev\Commands\StartCommand;
use PhpDev\Commands\StopCommand;
use PhpDev\Commands\SwitchCommand;
use PhpDev\Commands\UnlinkCommand;
use Silly\Application;

// Load correct autoloader depending on install location.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    // get from global composer vendor
    require getenv('HOME') . '/.config/composer/vendor/autoload.php';
}

// Setup container
require __DIR__ . '/Helper/Container.php';

// Version
$version = '1.0.0';

// Create application
$app = new Application('PhpDev', $version);

/**
 * Register commands
 */

// Install PhpDev services
$app->command('install', new InstallCommand())
    ->descriptions(InstallCommand::description());

/**
 * Most commands are available only if PhpDev is installed.
 */
if (is_dir(PHPDEV_HOME_PATH)) {
    // Start the PhpDev services.
    $app->command('start', new StartCommand())
        ->descriptions(StartCommand::description());

    // Stop the PhpDev services.
    $app->command('stop', new StopCommand())
        ->descriptions(StopCommand::description());

    // Restart the PhpDev services.
    $app->command('restart', new RestartCommand())
        ->descriptions(RestartCommand::description());

    // Show linked site
    $app->command('links', new LinksCommand())
        ->descriptions(LinksCommand::description());

    // Link site to PhpDev
    $app->command(
        'link [path] [-s|--site=] [-p|--php=] [-t|--tld=] [--ssl=]',
        new LinkCommand()
    )->descriptions(
        LinkCommand::description(),
        LinkCommand::descriptionArgOpt()
    );

    // Link site to PhpDev
    $app->command(
        'proxy site destination [-t|--tld=] [--ssl=]',
        new ProxyCommand()
    )->descriptions(
        ProxyCommand::description(),
        ProxyCommand::descriptionArgOpt()
    );

    // Unlink site
    $app->command('unlink site', new UnlinkCommand())
        ->descriptions(
            UnlinkCommand::description(),
            UnlinkCommand::descriptionArgOpt()
        );

    // Switch php version
    $app->command('switch site php', new SwitchCommand())
        ->descriptions(
            SwitchCommand::description(),
            SwitchCommand::descriptionArgOpt()
        );

    // Add SSL to the site
    $app->command('ssl site path', new SslCommand())
        ->descriptions(
            SslCommand::description(),
            SslCommand::descriptionArgOpt()
        );

    // Remove SSL to the site
    $app->command('ssl-remove site', new SslRemoveCommand())
        ->descriptions(
            SslRemoveCommand::description(),
            SslRemoveCommand::descriptionArgOpt()
        );
}
