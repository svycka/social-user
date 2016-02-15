# svycka/social-user

[![Build Status][ico-travis]][link-travis]

[![Coverage Status](https://coveralls.io/repos/github/svycka/social-user/badge.svg?branch=master)](https://coveralls.io/github/svycka/social-user?branch=master)
[![Quality Score][ico-code-quality]][link-code-quality]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

This module adds custom grant types for [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php) to allow login with social services like google or facebook.

## Install

Via Composer

``` bash
$ composer require svycka/social-user
```

## Basic Usage

- Register `Svycka\SocialUser` as module in `config/application.config.php`
- Copy the file located in `vendor/svycka/social-user/data/social_user.local.php.dist` to `config/autoload/social_user.local.php` and change the values as you wish

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Credits

- [Vytautas Stankus][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/svycka/social-user.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/svycka/social-user/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/svycka/social-user.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/svycka/social-user.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/svycka/social-user
[link-downloads]: https://packagist.org/packages/svycka/social-user
[link-travis]: https://travis-ci.org/svycka/social-user
[link-code-quality]: https://scrutinizer-ci.com/g/svycka/social-user
[link-author]: https://github.com/svycka
[link-contributors]: ../../contributors
