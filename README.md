# lum.test.php

## Summary

An extremely simple TAP based testing library.

It currently supports TAP version 12 (the version most libraries use.)
I have plans to add TAP version 13 at some point, but it's not there yet.

## Classes

| Class                   | Description                                       |
| ----------------------- | ------------------------------------------------- |
| Lum\Test                | A simple test library.                            |
| Lum\Test\Functional     | A way to use the tests in a functional style.     |
| Lum\Test\Harness        | A test harness for running test suites.           |

## lumtest

A binary called `lumtest` is added to the `vendor/bin` which can be used
to test your apps. It can take two parameters:

| Parameter | Description          | Default value                       |
| --------- | -------------------- | ----------------------------------- |
|  -d       | Directory for tests  | 'test'                              |
|  -e       | Extension for tests  | 'php'                               |

The extensions may have multiple names separated by a | character.

## Examples

See the various tests in `test` for examples of using the libraries.

## Testing

You can test this library set by running `composer test`.

## Official URLs

This library can be found in two places:

 * [Github](https://github.com/supernovus/lum.test.php)
 * [Packageist](https://packagist.org/packages/lum/lum-test)

## Author

Timothy Totten

## License

[MIT](https://spdx.org/licenses/MIT.html)
