## Introduction

This is a PHP tool which allows you to back up your Production/Staging database and restore it into your local database.

## Requirements

Make sure all dependencies have been installed before moving on:

* [PHP](http://php.net/manual/en/install.php) >= 5.6.4
* [Composer](https://getcomposer.org/download/)


### Install dependencies

Navigate to the cloned directory and run the followings from the command line:

```shell
$ composer install
```

## Configuration

Open `config.php` and add all the database information for both Dev and Production environments.

## Using the script

Just simply run `index.php`
