<?php

use Illuminate\Container\Container;
use PhpDev\App\Config;
use PhpDev\App\Nginx;
use PhpDev\App\PhpFpm;
use PhpDev\App\Site;

// Create container
$container = new Container();

// Register class
$container->singleton(Config::class, fn () => new Config());
$container->singleton(Nginx::class, fn () => new Nginx());
$container->singleton(PhpFpm::class, fn () => new PhpFpm());
$container->singleton(Site::class, fn () => new Site());

// Set container instance
Container::setInstance($container);
