<?php

namespace Arispati\Phpdev;

use Arispati\Phpdev\Drivers\CommandLine;

// Define constants.
if (! defined('PHPDEV_HOME_PATH')) {
    define('PHPDEV_HOME_PATH', $_SERVER['HOME'] . '/.config/phpdev');
}

define('BREW_PREFIX', (new CommandLine())->runCommand('printf $(brew --prefix)'));
