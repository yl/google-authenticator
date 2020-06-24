[![StyleCI](https://styleci.io/repos/106357176/shield?branch=master)](https://styleci.io/repos/106357176)
[![Build Status](https://www.travis-ci.org/yangliulnn/google-authenticator.svg?branch=master)](https://www.travis-ci.org/yangliulnn/google-authenticator)
[![Latest Stable Version](https://poser.pugx.org/leonis/google-authenticator/v/stable?format=flat-square)](https://packagist.org/packages/leonis/google-authenticator)
[![Total Downloads](https://poser.pugx.org/leonis/google-authenticator/downloads?format=flat-square)](https://packagist.org/packages/leonis/google-authenticator)
[![License](https://poser.pugx.org/leonis/google-authenticator/license?format=flat-square)](https://packagist.org/packages/leonis/google-authenticator)

# google-authenticator
Google Authenticator Forked from [PHPGangsta/GoogleAuthenticator](https://github.com/PHPGangsta/GoogleAuthenticator)

## Installation
```
composer require leonis/google-authenticator
```

## Usage
```php
use \Leonis\GoogleAuthenticator\GoogleAuthenticator;

$secret = GoogleAuthenticator::secret();

$qrCode = GoogleAuthenticator::qrCode('leonis', $secret)->writeDataUri();

$code = GoogleAuthenticator::code($secret);

$result = GoogleAuthenticator::verify($secret,$code);
```

## License
MIT
