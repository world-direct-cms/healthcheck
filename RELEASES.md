## Release 3.0
- !!! BREAKING CHANGE !!! - The TypoScript settings where moved into the extension configuration. There is no TypoScript `setup.typoscript` file anymore.
- !!! BREAKING CHANGE !!! - The secret setting was removed, as we do not use it. It is also anyway to less security for such an important topic. Therefore please use different mechanisms to secure your healthcheck endpoint.
- PHPStan was replaced with Psalm as static language checker
