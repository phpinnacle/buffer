# PHPinnacle Buffer

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

PHPinnacle Buffer is a simple tool for operating binary data in PHP. Mostly it simply wraps PHP pack/unpack functions.

Code is mostly based on [bunnyphp](https://github.com/jakubkulhan/bunny), Buffer class implemenation.

## Install

Via Composer

```bash
$ composer require phpinnacle/buffer
```

## Basic Usage

```php
<?php

use PHPinnacle\Buffer\ByteBuffer as Buffer;

// AMQP protocol header
$buffer = new Buffer;
$buffer
    ->append('AMQP')
    ->appendUint8(0)
    ->appendUint8(0)
    ->appendUint8(9)
    ->appendUint8(1)
;

```

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email dev@phpinnacle.com instead of using the issue tracker.

## Credits

- [PHPinnacle][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/phpinnacle/buffer.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/phpinnacle/buffer.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/phpinnacle/buffer.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/phpinnacle/buffer.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/phpinnacle/buffer
[link-scrutinizer]: https://scrutinizer-ci.com/g/phpinnacle/buffer/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/phpinnacle/buffer
[link-downloads]: https://packagist.org/packages/phpinnacle/buffer
[link-author]: https://github.com/phpinnacle
[link-contributors]: https://github.com/phpinnacle/buffer/graphs/contributors
