# Laravel Signer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/masterei/laravel-signer.svg?style=flat-square)](https://packagist.org/packages/masterei/laravel-signer)
[![Total Downloads](https://img.shields.io/packagist/dt/masterei/laravel-signer.svg?style=flat-square)](https://packagist.org/packages/masterei/laravel-signer)

Simple Laravel signed URL wrapper with additional feature such as consumable and user-based signed URLs.

## Installation

You can install the package via composer:

```bash
composer require masterei/laravel-signer
```

Optionally, you can publish the config file with:

```bash
php artisan vendor:publish --tag="signer-config"
```

## Usage

### Normal Usage

### Advance Usage

```php
Signer::route('subscribe')
    ->parameters(['user' => 1])     // additional parameters
    ->authenticated([1, 2])         // user ids as int or array, User model instance
    ->consumable(2)                 // number of times url can be accessed
    ->absolute(false)               // include domain on signature hashing; default: true
    ->prefixDomain(true)            // force url domain prefix on non absolute path; default: false
    ->expiration(now()->addDays(2)) // urls expiration period; accepts: Carbon/Carbon instance
    ->make();                       // finally create the url
```

### Native Signed URL
If you don't want to use the additional feature. Native `Signed URL` is a goto option.

Note: This does not store in database and will only be validated using the framework native validation method.
```php
Signer::signedRoute('subscribe', ['user' => 1]);

// You may exclude the domain from the signed URL hash
// by providing the `absolute` argument to the signedRoute method:
Signer::signedRoute('subscribe', ['user' => 1], absolute: false);

// If you would like to generate a temporary signed URL
// that expires after a specified amount of time.
Signer::temporarySignedRoute('subscribe', now()->addDay(), ['user' => 1]);
```



### Validating Signed URL

```php
// To ensure that the incoming request has a valid signature,
// you have to attach the middleware into the route for it to work.
Route::post('subscribe/{user}', function (Request $request) {
    // ...
})->name('subscribe')->middleware('signer');

// If your signed URL exclude the domain in the URL hash,
// you should provide the `relative` argument to the middleware.
Route::post('subscribe/{user}', function (Request $request) {
    // ...
})->name('subscribe')->middleware('signer:relative');

// Maybe there will be a time you want to forcefully disable the
// framework native validation, you should provide the `strict`
// argument to the middleware.
Route::post('subscribe/{user}', function (Request $request) {
    // ...
})->name('subscribe')->middleware('signer:strict');
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
