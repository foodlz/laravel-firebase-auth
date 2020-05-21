# laravel-firebase-auth
Secure your laravel API with Google Firebase Auth

Adding the *Middleware* to your API will ensure that access is granted only using a valid Bearer Token issues by Goggle Firebase Auth.

## Install

```bash
composer require csrui/laravel-firebase-auth
```

Publish the package's config.

```bash
php artisan vendor:publish
```

This will add a firebase.php config file where you need to add you Firebase **Project ID**.

## How to use

There are two ways to use this.

### 1. Lock access without JWT token

Add the *Middleware* on your *Kernel.php* file.

```php
\csrui\LaravelFirebaseAuth\Middleware\JWTAuth::class,
```

### 2. Lock access and identify the client requester

Add the Service Provider to your config/app.php

```php
csrui\LaravelFirebaseAuth\FirebaseAuthServiceProvider::class,
```

Register your new Guard on your AuthServiceProvider.php in the `boot` method.

```php
$this->app['auth']->viaRequest('firebase', function ($request) {
    return app(\csrui\LaravelFirebaseAuth\Guard::class)->user($request);
});
```

Now on you auth.php configure your API guard driver to 'firebase'.

```php
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'firebase',
            'provider' => 'firebase',
        ],
    ],
    
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],

        'firebase' => [
            'driver' => 'firebase',
            'model' => \csrui\LaravelFirebaseAuth\User::class,
        ],
],
```
```php
Add authentication to api routes in `/routes/api.php`.

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
    //return true;
});

Route::middleware('auth:api')->apiResource('endpoint', 'API\EndpointController');
```
## Support

Feel free to open issues and provide feedback.
