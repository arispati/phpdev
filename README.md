# PhpDev
PhpDev is a local PHP development environment inspired by [Laravel Valet](https://github.com/laravel/valet) for WSL on Windows.

## Installation
> PhpDev requires WSL on Windows with Ubuntu OS, [Nginx](https://nginx.org) and [Homebrew](https://brew.sh).
> 
> Before installation, you should make sure that no other programs such as Apache that binding to your local machine's port 80.

To get started, you first need to ensure that Homebrew is up to date using the `update` command:
```bash
brew update
```

Next, you should use Homebrew to install PHP:
```bash
brew install php
```

After installing PHP, you are ready to install the [Composer package manager](https://getcomposer.org). After Composer has been installed, you may install PhpDev as a global Composer package:
```bash
composer global require arispati/phpdev
```
> Make sure the `$HOME/.config/composer/vendor/bin` directory is in your system's "PATH".

Finally, you may execute PhpDev's install command. This will configure and install PhpDev services. In addition, the daemons PhpDev depends on will be configured to launch when your system starts:
```bash
phpdev install
```

## Upgrading
You may update your PhpDev installation by executing the `composer global require arispati/phpdev` command in your terminal. After upgrading, it is good practice to run the `phpdev install` command so PhpDev can make additional upgrades to your configuration files if necessary.

## Commands
| Command | Description                        |
|---------|------------------------------------|
| install | Install PhpDev services            |
| start   | Start PhpDev services              |
| stop    | Stop PhpDev services               |
| restart | Restart PhpDev services            |
| links   | Show all linked sites              |
| [link](#link-command)    | Link the current working directory |
| [proxy](#proxy-command)   | Add proxy site                     |
| [unlink](#unlink-command)  | Unlink site                        |
| [switch](#switch-command)  | Switch PHP version for the site    |
| list    | Display a list of all commands     |

### Link Command

`phpdev link [path] [-s|--site=] [-p|--php=]`

| Arguments  | Description                      | Default                |
|------------|----------------------------------|------------------------|
| path       | Root directory path for the site | Current directory path |
| -s, --site | Site name                        | Current directory name |
| -p, --php  | Which php version to use         | Current php version    |

Example:
```bash
phpdev link public -s laravel -p 8.4
```

> Link `laravel.test` with `public` folder as web root directory

:information_source: You still have to add `laravel.test` to the `hosts` file on windows

### Proxy Command

`phpdev proxy site destination`

| Arguments   | Description       |
|-------------|-------------------|
| site        | Site name         |
| destination | Proxy destination |

Example:
```bash
phpdev proxy laravel http://127.0.0.1:8000
```

> Proxy `laravel.test` to `http://127.0.0.1:8000`

### Unlink Command

`phpdev unlink site`

| Arguments   | Description       |
|-------------|-------------------|
| site        | Site name         |

Example:
```bash
phpdev unlink laravel
```

> Remove `laravel.test`

### Switch command

`phpdev switch site php`

| Arguments   | Description       |
|-------------|-------------------|
| site        | Site name         |
| php         | PHP version       |

Example:
```bash
phpdev switch laravel 8.4
```

> Switch php version of `laravel.test` to `PHP 8.4`
