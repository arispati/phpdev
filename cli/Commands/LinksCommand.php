<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Helper\Helper;

class LinksCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Show all linked sites';
    }

    public function __invoke()
    {
        $headers = ['name', 'type', 'php', 'ssl', 'path'];
        $sites = array_map(function ($item) use ($headers) {
            $result = [];
            foreach ($headers as $header) {
                $output = $item[$header] ?? '-';
                if (is_bool($output)) {
                    $output = $output ? '✓' : '-';
                }
                $result[] = $output;
            }
            return $result;
        }, Config::read('sites'));
        // sort
        usort($sites, function ($a, $b) {
            return $a[4] <=> $b[4];
        });
        // show table
        Helper::table($headers, array_values($sites));
    }
}
