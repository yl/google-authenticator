[![StyleCI](https://styleci.io/repos/106357176/shield?branch=master)](https://styleci.io/repos/106357176)
[![Build Status](https://www.travis-ci.org/yangliulnn/google-authenticator.svg?branch=master)](https://www.travis-ci.org/yangliulnn/google-authenticator)
[![Latest Stable Version](https://poser.pugx.org/leonis/google-authenticator/v/stable?format=flat-square)](https://packagist.org/packages/leonis/google-authenticator)
[![Total Downloads](https://poser.pugx.org/leonis/google-authenticator/downloads?format=flat-square)](https://packagist.org/packages/leonis/google-authenticator)
[![License](https://poser.pugx.org/leonis/google-authenticator/license?format=flat-square)](https://packagist.org/packages/leonis/google-authenticator)

# google-authenticator
Google Authenticator

## Installation
```
composer require leonis/google-authenticator
```

## Usage
```php
$authenticator = new \Leonis\GoogleAuthenticator\GoogleAuthenticator();

$secret = $authenticator->secret();

$qrCode = $authenticator->qrCode('leonis', $secret)->writeDataUri();

$code = $authenticator->code($secret);

$result = $authenticator->verify($secret,$code);
```

## License
MIT
