<?php

namespace PhpDev\Helper;

use Illuminate\Container\Container;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class Helper
{
    /**
     * Writer
     *
     * @return ConsoleOutput
     */
    protected static function writer(): ConsoleOutput
    {
        // get container
        $container = Container::getInstance();
        // if writer abstract not bounded yet
        if (! $container->bound('writer')) {
            // register to the container
            $container->instance('writer', new ConsoleOutput());
        }
        // resolve abstract
        return $container->make('writer');
    }

    /**
     * Write the given text to the console
     *
     * @param string $text
     * @return void
     */
    public static function write(string $text = ''): void
    {
        self::writer()->writeln($text);
    }

    /**
     * Write the given text to the console
     *
     * @param string $text
     * @return void
     */
    public static function info(string $text = ''): void
    {
        self::writer()->writeln(sprintf('<info>%s</info>', $text));
    }

    /**
     * Write the given text to the console
     *
     * @param string $text
     * @return void
     */
    public static function warning(string $text = ''): void
    {
        self::writer()->writeln(sprintf('<fg=red>%s</>', $text));
    }

    /**
     * Write table to the console
     *
     * @param array $headers
     * @param array $rows
     * @return void
     */
    public static function table(array $headers, array $rows = []): void
    {
        // create table instance
        $table = new Table(self::writer());
        // set headers and rows
        $table->setHeaders($headers)->setRows($rows);
        // render the table
        $table->render();
    }

    /**
     * Tab the given value
     *
     * @param mixed $value
     * @param callable $callback
     * @return mixed
     */
    public static function tab(mixed $value, callable $callback): mixed
    {
        $callback($value);

        return $value;
    }

    /**
     * Determine if a given string end with a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     * @return boolean
     */
    public static function endWith(string $haystack, array|string $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string start with a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     * @return boolean
     */
    public static function startWith(string $haystack, array|string $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     * @return boolean
     */
    public static function contains(string $haystack, array|string $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
