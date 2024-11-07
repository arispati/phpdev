<?php

// Allow bypassing these checks if using Phpdev in a non-CLI app
if (php_sapi_name() !== 'cli') {
    return;
}

/**
 * Check the system's compatibility with Phpdev.
 */
$inTestingEnvironment = strpos($_SERVER['SCRIPT_NAME'], 'phpunit') !== false;

$inWslEnvironment = str_contains(strtolower(exec('uname -r')), 'microsoft');

if (PHP_OS !== 'Linux' && ! $inWslEnvironment && ! $inTestingEnvironment) {
    echo 'Phpdev only supports WSL.' . PHP_EOL;

    exit(1);
}

if (version_compare(PHP_VERSION, '8.0', '<')) {
    echo 'Phpdev requires PHP 8.0 or later.';

    exit(1);
}

if (exec('which nginx') == '' && ! $inTestingEnvironment) {
    echo 'Phpdev requires Nginx to be installed on your WSL.';

    exit(1);
}

if (exec('which brew') == '' && ! $inTestingEnvironment) {
    echo 'Phpdev requires Homebrew to be installed on your WSL.';

    exit(1);
}
