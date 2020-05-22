# laravel-firebase-auth
Secure your laravel API with Google Firebase Auth

Adding the *Middleware* to your API will ensure that access is granted only using a valid Bearer Token issues by Goggle Firebase Auth.

## Install
Add the following to your `composer.json`.
```json
"require: {
    "sdwru/laravel-firebase-auth": "dev-master"
},
"repositories": [
        {"type": "git", "url": "https://github.com/sdwru/laravel-firebase-auth.git"}
]
```
and run `composer update`
#### firebase-php
Publish the the firebase-php admin SDK package config.

```bash
php artisan vendor:publish
```

Configure firebase-php admin SDK [according to their instructions](https://firebase-php.readthedocs.io/en/stable/setup.html#google-service-account) if you have not already done so.  This involves adding the Firebase SDK `package.json` file, [as explained here](https://firebase.google.com/docs/admin/setup#add_firebase_to_your_app) somewhere, such as your root laravel directory, and adding a reference to that file in your .env
```bash
FIREBASE_CREDENTIALS=myproject-firebase-adminsdk.json
```

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

Register the Guard in AuthServiceProvider.php in the `boot` method.

```php
$this->app['auth']->viaRequest('firebase', function ($request) {
    return app(\csrui\LaravelFirebaseAuth\Guard::class)->user($request);
});
```

On `config/auth.php` set your api guard driver to 'firebase'.

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
Add authentication to api routes in `routes/api.php`.
```php
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
    //return true;
});

Route::middleware('auth:api')->apiResource('some_endpoint', 'API\SomeEndpointController');
```
### Use it
For example

```php
<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

class UserController extends Controller
{
    public function index(Request $request, Guard $guard)
    {
        
        // Retrieve Firebase uid from id token via request
        $user = $request->user();
        $uid = $user->getAuthIdentifier()
        
        // Or using guard
        $user = $guard->user();
        $uid = $user->getAuthIdentifier();
        
        
        // Do something with the request for this user
    }
}
```
## Support

Feel free to open issues and provide feedback.
