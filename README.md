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

## Examples

See the `runtests.php` for an example of how to build a top level test
runner using the Lum\Test\Harness library. See the various tests in `test`
for examples of using the other libraries.

## Testing

You can test this library set by running `composer test` or `php runtests.php`.
They both do the same thing, so it's up to you.

## Official URLs

This library can be found in two places:

 * [Github](https://github.com/supernovus/lum.test.php)
 * [Packageist](https://packagist.org/packages/lum/lum-test)

## Author

Timothy Totten

## License

[MIT](https://spdx.org/licenses/MIT.html)
