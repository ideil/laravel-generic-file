LaravelGenericFile
==================

Also there is a version in Russian: [README.ru.md](README.ru.md)

## Requirements
This package currently requires php >= 5.4 and Laravel 5

## Installation
LaravelGenericFile is distributed as a composer package, which is how it should be used in your app.

Install the package using Composer. Edit your project's `composer.json` file to require `ideil/laravel-generic-file`.

```js
  "require": {
    "laravel/framework": "5.*",
    "ideil/laravel-generic-file": "0.*"
  }
```

Once this operation completes, the final step is to add the service provider. Open `config/app.php`, and add a new item to the providers array.

```php
    'Ideil\LaravelGenericFile\LaravelGenericFileServiceProvider'
```

Configuration files can be published and edited by running:

```
php artisan vendor:publish
```
