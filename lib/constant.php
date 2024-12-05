<?php

// Define constants.
if (! defined('PHPDEV_HOME_PATH')) {
    define('PHPDEV_HOME_PATH', $_SERVER['HOME'] . '/.config/phpdev');
}

// Phpdev config path
if (! defined('PHPDEV_CONFIG_PATH')) {
    define('PHPDEV_CONFIG_PATH', PHPDEV_HOME_PATH . '/config.json');
}

// Phpdev current directory path
if (! defined('PHPDEV_CURRENT_DIR_PATH')) {
    define('PHPDEV_CURRENT_DIR_PATH', getcwd());
}

// TLD config
if (! defined('PHPDEV_TLD')) {
    define('PHPDEV_TLD', 'test');
}

// Current user
if (! defined('PHPDEV_USER')) {
    define('PHPDEV_USER', exec('whoami'));
}

// Stubs path
if (! defined('PHPDEV_STUB_PATH')) {
    define('PHPDEV_STUB_PATH', __DIR__ . '/../stubs');
}

// Current PHP version
if (! defined('PHPDEV_PHP_VERSION')) {
    define('PHPDEV_PHP_VERSION', sprintf('%s.%s', PHP_MAJOR_VERSION, PHP_MINOR_VERSION));
}

// Brew path
if (! defined('PHPDEV_BREW_PATH')) {
    define('PHPDEV_BREW_PATH', exec('brew --prefix'));
}

// PHP path
if (! defined('PHPDEV_PHP_PATH')) {
    define('PHPDEV_PHP_PATH', PHPDEV_BREW_PATH . '/etc/php');
}

// Nginx config path
if (! defined('PHPDEV_NGINX_CONF_PATH')) {
    define('PHPDEV_NGINX_CONF_PATH', '/etc/nginx/nginx.conf');
}

// Nginx site path
if (! defined('PHPDEV_NGINX_SITE_PATH')) {
    define('PHPDEV_NGINX_SITE_PATH', '/etc/nginx/sites-enabled');
}
