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
            echo '!bound' . PHP_EOL;
            $container->instance('writer', new ConsoleOutput());
        }
        // resolve abstract
        echo 'make' . PHP_EOL;
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
}
