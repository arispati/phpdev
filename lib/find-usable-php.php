<?php

// Minimum PHP version
$minimumPhpVersion = '8.3';

// First, check if the system's linked "php" is 8+; if so, return that.
// This is the most likely, most ideal, and fastest possible case
$linkedPhpVersion = shell_exec('php -r "echo phpversion();"');

if (version_compare($linkedPhpVersion, $minimumPhpVersion) >= 0) {
    echo exec('which php');

    return;
}

// If not, let's find it whether we have a version of PHP installed that's 8+;
// all users that run through this code path will see PhpDev run more slowly
$phps = explode(PHP_EOL, trim(shell_exec('brew list --formula | grep php')));

// Get by the minimum version if any
$minimumPhpFormula = sprintf('php@%s', $minimumPhpVersion);

if (in_array($minimumPhpFormula, $phps)) {
    echo getPhpExecutablePath($minimumPhpFormula);
    return;
}

// Get latest php version that greater than minimum version;
// Normalize version numbers
$phps = array_reduce($phps, function ($carry, $php) {
    $carry[$php] = getPhpVersionFromBrewFormula($php);

    return $carry;
}, []);

// Filter out older versions of PHP
$modernPhps = array_filter($phps, function ($php) use ($minimumPhpVersion) {
    return version_compare($php, 7.1) >= 0;
});

// If we don't have any modern versions of PHP, throw an error
if (empty($modernPhps)) {
    throw new Exception(sprintf(
        'Sorry, but you do not have a version of PHP installed that is compatible with PhpDev (%s)',
        $minimumPhpVersion
    ));
}

uasort($modernPhps, function ($a, $b) {
    return $b <=> $a;
});

// Get latest version
$phpLatest = reset($modernPhps);

echo getPhpExecutablePath(array_search($phpLatest, $modernPhps));

/**
 * Extract PHP executable path from PHP Version.
 *
 * @param  string  $phpFormulaName  For example, "php@8.1"
 * @return string
 */
function getPhpExecutablePath(string $phpFormulaName): string
{
    $path = sprintf('%s/opt/%s/bin/php', exec('brew --prefix'), $phpFormulaName);

    // validate bin php path
    if (file_exists($path)) {
        return $path;
    }

    throw new Exception("Cannot find an executable path for provided PHP version: {$phpFormulaName}");
}

/**
 * Extract PHP version from brew formula
 *
 * @param string $formulaName
 * @return string|null
 */
function getPhpVersionFromBrewFormula(string $formulaName): ?string
{
    if ($formulaName === 'php') {
        // Figure out its link
        $details = json_decode(shell_exec("brew info $formulaName --json"));

        if (! empty($details[0]->aliases[0])) {
            $formulaName = $details[0]->aliases[0];
        } else {
            return null;
        }
    }

    if (strpos($formulaName, 'php@') === false) {
        return null;
    }

    return substr($formulaName, strpos($formulaName, '@') + 1);
}
