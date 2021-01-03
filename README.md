# PHPinnacle Buffer

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

PHPinnacle Buffer is a simple tool for operating binary data in PHP. Mostly it simply wraps PHP pack/unpack functions.

## Install

Via Composer

```bash
$ composer require phpinnacle/buffer
```

## Basic Usage

```php
<?php

use PHPinnacle\Buffer\ByteBuffer;

// AMQP protocol header
$buffer = new ByteBuffer;
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

## Benchmarks

```bash
$ composer bench
```

Some results with pure PHP realisation:

| Benchmark | Best (μs) | Mean (μs) | Mode (μs) | Worst (μs) |
|---|---|---|---|---|
| appendIntegers | 11.605 | 12.115 | 12.047 | 12.888  |
| appendFloats   | 10.464 | 10.913 | 10.786 | 17.943  |
| appendString   | 8.857  | 41.021 | 20.611 | 362.174 |
| consume        | 48.916 | 50.721 | 50.399 | 61.542  |
| read           | 26.617 | 27.665 | 27.500 | 31.744  |

And results with enabled [extension][link-extension]:

| Benchmark | Best (μs) | Mean (μs) | Mode (μs) | Worst (μs) |
|---|---|---|---|---|
| appendIntegers | 2.522  | 2.657  | 2.625  | 3.031  |
| appendFloats   | 1.987  | 2.136  | 2.095  | 3.307  |
| appendString   | 3.692  | 3.854  | 3.806  | 5.695  |
| consume        | 13.701 | 14.654 | 14.454 | 17.977 |
| read           | 5.128  | 5.425  | 5.313  | 6.625  |

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](.github/CONDUCT.md) for details.

## Security

If you discover any security related issues, please email dev@phpinnacle.com instead of using the issue tracker.

## Credits

- [PHPinnacle][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/phpinnacle/buffer.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/phpinnacle/buffer.svg?style=flat-square

[link-extension]: https://github.com/phpinnacle/ext-buffer
[link-packagist]: https://packagist.org/packages/phpinnacle/buffer
[link-downloads]: https://packagist.org/packages/phpinnacle/buffer
[link-author]: https://github.com/phpinnacle
[link-contributors]: https://github.com/phpinnacle/buffer/graphs/contributors
