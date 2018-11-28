# PHP to Zephir

[![Build Status](https://travis-ci.org/sandrokeil/php-to-zephir.svg?branch=master)](https://travis-ci.org/sandrokeil/php-to-zephir)
[![Coverage Status](https://coveralls.io/repos/sandrokeil/php-to-zephir/badge.svg?branch=master&service=github)](https://coveralls.io/github/sandrokeil/php-to-zephir?branch=master)

Converts PHP 7 files to [Zephir](https://zephir-lang.com/en) zep files
and can create Zephir prototype files of external used libraries.

## Requirements

- PHP >= 7.1

## Installation

```
$ composer require --dev sandrokeil/php-to-zephir
```

## Usage
To create Zephir zep files of your PHP files run:

```
$ bin/php2zephir php2zephir:zep:create [source path/file] [destination path/file]
```

To create Zephir prototypes for external libraries run:

```
$ bin/php2zephir php2zephir:prototype:create [source path/file] [destination file prototype.php]
```

## Create PHAR
A PHAR file can be generated with [box](https://github.com/humbug/box).

```
$ php box.phar compile
```

## Unit Tests

```
$ docker-compose run --rm php vendor/bin/phpunit
```

## Zephir Docker Image

If you want to compile your Zephir zep files for PHP Alpine 3.8 Docker images try my [Zephir Docker images](https://github.com/sandrokeil/docker-files/tree/master/zephir).

## Limitations

- Can not handle reserved variable names like `$resource`, it would result in compilation error