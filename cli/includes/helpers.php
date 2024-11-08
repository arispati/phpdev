<?php

namespace Arispati\Phpdev;

use Arispati\Phpdev\Tools\CommandLine;
use Symfony\Component\Console\Output\OutputInterface;

// Define constants.
if (! defined('PHPDEV_HOME_PATH')) {
    define('PHPDEV_HOME_PATH', $_SERVER['HOME'] . '/.config/phpdev');
}

// Phpdev config path
if (! defined('PHPDEV_DATA_PATH')) {
    define('PHPDEV_DATA_PATH', PHPDEV_HOME_PATH . '/phpdev.json');
}

// Phpdev current directory path
if (! defined('PHPDEV_CURRENT_DIR_PATH')) {
    define('PHPDEV_CURRENT_DIR_PATH', getcwd());
}

// TLD config
if (! defined('PHPDEV_TLD')) {
    define('PHPDEV_TLD', 'test');
}

// Brew path
if (! defined('PHPDEV_BREW_PATH')) {
    define('PHPDEV_BREW_PATH', (new CommandLine())->runCommand('printf $(brew --prefix)'));
}

// functions
/**
 * Determine if a given string starts with a given substring.
 */
function starts_with(string $haystack, array|string $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
            return true;
        }
    }

    return false;
}
