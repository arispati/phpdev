<?php

// Minimum PHP version
$minimumPhpVersion = '8.3';

// Allow bypassing these checks if using PhpDev in a non-CLI app
if (php_sapi_name() !== 'cli') {
    return;
}

/**
 * Check the system's compatibility with PhpDev.
 */
$inTestingEnvironment = strpos($_SERVER['SCRIPT_NAME'], 'phpunit') !== false;

$inWslEnvironment = str_contains(strtolower(exec('uname -r')), 'microsoft');

if (PHP_OS !== 'Linux' && ! $inWslEnvironment && ! $inTestingEnvironment) {
    echo 'PhpDev only supports Linux or WSL.' . PHP_EOL;

    exit(1);
} else {
    // ensure current OS is ubuntu
    if (exec('grep "ID=ubuntu" /etc/os-release') == '') {
        echo 'PhpDev only supports Ubuntu.' . PHP_EOL;

        exit(1);
    }
}

if (version_compare(PHP_VERSION, $minimumPhpVersion, '<')) {
    echo sprintf('PhpDev requires PHP %s or later.', $minimumPhpVersion) . PHP_EOL;

    exit(1);
}

if (exec('which nginx') == '' && ! $inTestingEnvironment) {
    echo 'PhpDev requires Nginx to be installed on your system.' . PHP_EOL;

    exit(1);
} else {
    // ensure that nginx installed from sudo apt install nginx
    if (exec('which nginx') != '/usr/sbin/nginx') {
        echo 'Invalid nginx service, please install it from Ubuntu\'s Advanced Packaging Tool (APT).' . PHP_EOL;

        exit(1);
    }
}

if (exec('which brew') == '' && ! $inTestingEnvironment) {
    echo 'PhpDev requires Homebrew to be installed on your system.' . PHP_EOL;

    exit(1);
}
