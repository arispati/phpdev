<?php

namespace PhpDev\Helper;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class File
{
    /**
     * Touch the given path
     *
     * @param string $path
     * @return void
     */
    public static function touch(string $path): void
    {
        touch($path);
    }

    /**
     * Determine if the given path is a directory
     *
     * @param string $path
     * @return boolean
     */
    public static function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Create a directory
     *
     * @param string $path
     * @param integer $mode
     * @return void
     */
    public static function mkdir(string $path, int $mode = 0755): void
    {
        mkdir($path, $mode, true);
    }

    /**
     * Ensure that the given directory exists
     *
     * @param string $path
     * @param integer $mode
     * @return void
     */
    public static function ensureDirExists(string $path, int $mode = 0755): void
    {
        if (! self::isDir($path)) {
            self::mkdir($path, $mode);
        }
    }

    /**
     * Determine if the given file exists
     *
     * @param string $path
     * @return boolean
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Read the contents of the given file
     *
     * @param string $path
     * @return string
     */
    public static function get(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * Write to the given file
     *
     * @param string $path
     * @param string $contents
     * @return void
     */
    public static function put(string $path, string $contents): void
    {
        file_put_contents($path, $contents);
    }

    /**
     * Delete the file at the given path
     *
     * @param string $path
     * @return void
     */
    public static function unlink(string $path): void
    {
        if (file_exists($path) || is_link($path)) {
            @unlink($path);
        }
    }

    /**
     * Get custom stub file if exists
     *
     * @param string $filename
     * @return string
     */
    public static function getStub(string $filename): string
    {
        $path = sprintf('%s/%s', PHPDEV_STUB_PATH, $filename);

        return self::get($path);
    }

    /**
     * Delete directory
     *
     * @param string $dirPath
     * @return void
     */
    public static function deleteDirectory(string $dirPath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        // iterate paths
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        // remove dir
        rmdir($dirPath);
    }
}
