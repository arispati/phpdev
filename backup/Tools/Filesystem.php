<?php

namespace PhpDev\Tools;

class Filesystem
{
    /**
     * Determine if the given path is a directory.
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Create a directory.
     */
    public function mkdir(string $path, ?string $owner = null, int $mode = 0755): void
    {
        mkdir($path, $mode, true);

        if ($owner) {
            $this->chown($path, $owner);
        }
    }

    /**
     * Determine if the given path is a symbolic link.
     */
    public function isLink(string $path): bool
    {
        return is_link($path);
    }

    /**
     * Resolve the given symbolic link.
     */
    public function readLink(string $path): string
    {
        return readlink($path);
    }

    /**
     * Ensure that the given directory exists.
     */
    public function ensureDirExists(string $path, ?string $owner = null, int $mode = 0755): void
    {
        if (! $this->isDir($path)) {
            $this->mkdir($path, $owner, $mode);
        }
    }

    /**
     * Determine if the given file exists.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Read the contents of the given file.
     */
    public function get(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * Write to the given file.
     */
    public function put(string $path, string $contents, ?string $owner = null): void
    {
        file_put_contents($path, $contents);

        if ($owner) {
            $this->chown($path, $owner);
        }
    }

    /**
     * Write to the given file as the non-root user.
     */
    public function putAsUser(string $path, ?string $contents): void
    {
        $this->put($path, $contents);
    }

    /**
     * Change the owner of the given path.
     */
    public function chown(string $path, string $user): void
    {
        chown($path, $user);
    }

    /**
     * Get custom stub file if exists.
     */
    public function getStub(string $filename): string
    {
        $path = __DIR__ . '/../stubs/' . $filename;

        return $this->get($path);
    }
}
