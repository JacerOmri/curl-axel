# CurlAxel

> 🐘 PHP based download accelerator ⏬

## Getting Started

This library is available as a composer package. Will add a standalone version with each release.

### Prerequisites

You only need php (with curl and mbstring extensions) and composer.

### Installing

Use composer to install it

```
composer require jaceromri/CurlAxel
```

And use it

```php
$c = new \CurlAxel\CurlAxel('http://ovh.net/files/1Mio.dat', 'download.dat');
$c->download();
```

## Running the tests

use phpunit against `tests` folder

```
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```
### Coding style tests

use phpcs against `src` folder

```
./vendor/bin/phpcs src
```

## Roadmap

The primary goal for now is to get a good initial version of this lib

* Better API
* Fix code style
* Add documentation
* Better exception handling
* Add server checks and fallback

## Contributing

Please read [CONTRIBUTING.md](https://github.com/JacerOmri/CurlAxel/blob/master/CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/JacerOmri/CurlAxel/tags).

## Authors

* **Jacer Omri** - *Initial work* - [jaceromri](https://github.com/jaceromri)

See also the list of [contributors](https://github.com/jaceromri/CurlAxel/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details