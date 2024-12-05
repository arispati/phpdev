<?php

namespace PhpDev\Helper;

class File
{
    /**
     * Determine if the given path is a directory.
     *
     * @param string $path
     * @return boolean
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @param integer $mode
     * @return never
     */
    public function mkdir(string $path, int $mode = 0755): never
    {
        mkdir($path, $mode, true);
    }

    /**
     * Ensure that the given directory exists.
     *
     * @param string $path
     * @param integer $mode
     * @return never
     */
    public function ensureDirExists(string $path, int $mode = 0755): never
    {
        if (! $this->isDir($path)) {
            $this->mkdir($path, $mode);
        }
    }

    /**
     * Determine if the given file exists.
     *
     * @param string $path
     * @return boolean
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Read the contents of the given file.
     *
     * @param string $path
     * @return string
     */
    public function get(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * Write to the given file.
     *
     * @param string $path
     * @param string $contents
     * @return never
     */
    public function put(string $path, string $contents): never
    {
        file_put_contents($path, $contents);
    }
}
