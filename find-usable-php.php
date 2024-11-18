<?php

$minimumPhpVersion = '8.3';

// First, check if the system's linked "php" is 8+; if so, return that. This
// is the most likely, most ideal, and fastest possible case
$linkedPhpVersion = shell_exec('php -r "echo phpversion();"');

if (version_compare($linkedPhpVersion, $minimumPhpVersion) >= 0) {
    echo exec('which php');

    return;
}

// If we don't have any versions of linked PHP, throw an error
throw new Exception(
    "Sorry, but you do not have a version of PHP installed that is compatible with Phpdev (>= {$minimumPhpVersion})."
);
