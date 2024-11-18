<?php

namespace PhpDev;

use PhpDev\Tools\CommandLine;
use Illuminate\Container\Container;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

// Define constants.
if (! defined('PHPDEV_HOME_PATH')) {
    define('PHPDEV_HOME_PATH', $_SERVER['HOME'] . '/.config/phpdev');
}

// Phpdev config path
if (! defined('PHPDEV_CONFIG_PATH')) {
    define('PHPDEV_CONFIG_PATH', PHPDEV_HOME_PATH . '/config.json');
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

/**
 * Set or get a global console writer
 */
function writer(?OutputInterface $writer = null): OutputInterface|null
{
    $container = Container::getInstance();

    if (! $writer) {
        if (! $container->bound('writer')) {
            $container->instance('writer', new ConsoleOutput());
        }

        return $container->make('writer');
    }

    $container->instance('writer', $writer);

    return null;
}

/**
 * Output the given text to the console.
 */
function output(?string $output = ''): void
{
    writer()->writeln($output);
}

/**
 * Output the given text to the console.
 */
function info($output): void
{
    output('<info>' . $output . '</info>');
}

/**
 * Output the given text to the console.
 */
function warning(string $output): void
{
    output('<fg=red>' . $output . '</>');
}

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

/**
 * Tap the given value.
 */
function tap(mixed $value, callable $callback): mixed
{
    $callback($value);

    return $value;
}

/**
 * Get the user.
 */
function user(): string
{
    return $_SERVER['USER'];
}
