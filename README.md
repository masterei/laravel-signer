# Laravel Signer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/masterei/laravel-signer.svg?style=flat-square)](https://packagist.org/packages/masterei/laravel-signer)
[![Total Downloads](https://img.shields.io/packagist/dt/masterei/laravel-signer.svg?style=flat-square)](https://packagist.org/packages/masterei/laravel-signer)

Laravel signed URL wrapper with additional feature such as consumable and user-based signed URLs.

## Installation
You can install the package via composer:
```bash
composer require masterei/laravel-signer
```

You need to publish the migration to create the package table:
```bash
php artisan vendor:publish --tag="signer-migration"
```

After that, you need to run migration command.
```bash
php artisan migrate
```

Optionally, you can publish the config file with:
```bash
php artisan vendor:publish --tag="signer-config"
```

## Usage

### Basic Usage
```php
use App\Models\User;
use Masterei\Signer\Signer;

// creating signed url that can only be accessed by limited number of times
Signer::consumableRoute('subscribe', 1, ['user' => 1]);

// expires after a specified amount of time
Signer::temporaryConsumableRoute('subscribe', now()->addMinute(), 1, ['user' => 1]);


// creating signed url that can only be access by certain specified user/s
$user = User::first();
Signer::authenticatedRoute('subscribe', $user, ['user' => 1]); 

// note: user parameter can accept; user id as int or array, model, collection

// expires after a specified amount of time
Signer::temporaryAuthenticatedRoute('subscribe', now()->addMinute(), $user, ['user' => 1]);
```

#### Additional Arguments
As usual, you may exclude the domain from the signed URL hash by providing the `absolute` argument 
to the class method.
```php
return Signer::consumableRoute('subscribe', 1, ['user' => 1], absolute: false);
```

You may also want to force the domain prefix, even if you excluded the domain from the signed URL hash 
by providing the `prefixDomain` argument to the class method.
```php
return Signer::consumableRoute('subscribe', 1, ['user' => 1], prefixDomain: true);
```

### Advance Usage
```php
return Signer::route('subscribe')   // route name
    ->parameters(['user' => 1])     // additional parameters
    ->authenticated([1, 2])         // user id as int or array, model, collection
    ->consumable(2)                 // number of times url can be accessed
    ->absolute(false)               // include domain on signature hashing; default: true
    ->prefixDomain(true)            // force url domain prefix on non absolute path; default: false
    ->expiration(now()->addDays(2)) // urls expiration period; accepts: Carbon/Carbon instance
    ->make();                       // finally create the url
```

### Native Signed URL
If you don't want to use the additional feature. Native `signedRoute` and `temporarySignedRoute` is a goto option.

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

### Validating Signed Route Requests

#### Package Signed Route
```php
// To ensure that the incoming request has a valid signature,
// you have to include the middleware into the route for it to work.
Route::post('subscribe/{user}', function (Request $request) {
    // ...
})->name('subscribe')->middleware('signer');

// Sometimes, you want to forcefully disable the framework native validation,
// you should provide the `strict` argument to the middleware parameter.
Route::post('subscribe/{user}', function (Request $request) {
    // ...
})->name('subscribe')->middleware('signer:strict');
```

#### Native Signed Route
If your route uses the native signed url method namely `signedRoute` and `temporarySignedRoute`; 
and exclude the domain from the signed URL hash you should provide the `relative` argument to the
middleware for it to work.
```php
Route::post('subscribe/{user}', function (Request $request) {
    // ...
})->name('subscribe')->middleware('signer:relative');
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
