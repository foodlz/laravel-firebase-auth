# laravel-firebase-auth
Secure your laravel API with Google Firebase Auth

Adding the *Middleware* to your API will ensure that access is granted only using a valid Bearer Token issues by Goggle Firebase Auth.

The main difference between this package and the package we forked it from is that we are using [laravel-firebase](https://github.com/kreait/laravel-firebase) as a dependency which in turn depends on [firebase-php](https://github.com/kreait/firebase-php).  Using that package instead of firebase-tokens (which is already included in firebase-php) simplifiest this package by removing the need for a service provider.  Since that package depends on [firebase-php](https://github.com/kreait/firebase-php), you can use [all the features that package provides](https://github.com/kreait/firebase-php#documentation).

## Install
```bash
composer require sdwru/laravel-firebase-auth
```
#### laravel-firebase
Publish the laravel-firebase package config if not already done so.

```bash
php artisan vendor:publish
```

Configure laravel-firebase [according to their instructions](https://github.com/kreait/laravel-firebase/blob/master/README.md).  This involves adding the Firebase admin SDK `package.json` file, [as explained here](https://firebase.google.com/docs/admin/setup#initialize-sdk).

In other words, generate a JSON file as follows:

1. In the Firebase console, open Settings > Service Accounts.
2. Click Generate New Private Key, then confirm by clicking Generate Key.
3. Securely store the JSON file containing the key somewhere on your server, such as your root laravel directory, and add a reference to that file in your laravel `.env` file
```bash
FIREBASE_CREDENTIALS=myproject-firebase-adminsdk.json
```

## How to use

There are two ways to use this.

### Method 1. Lock access without JWT token

Add the *Middleware* on your app/Http/*Kernel.php* file.

For applying to `api` auth 
```php
'api' => [
    \sdwru\LaravelFirebaseAuth\Middleware\JWTAuth::class,
],
```
And add api authentication in routes/api.php as you normally would.
```php
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
    //return true;
});

Route::middleware('auth:api')->apiResource('some_endpoint', 'API\SomeEndpointController');
```
Or use a custom auth name (such as `firebase`)
```php
prtected '$routeMiddleware' = [
    'firebase' => \sdwru\LaravelFirebaseAuth\Middleware\JWTAuth::class,
],
```
Then in routes/api.php
```php
Route::middleware('firebase')->get('/user', function (Request $request) {
    return $request->user();
    //return true;
});

Route::middleware('firebase')->apiResource('some_endpoint', 'API\SomeEndpointController');
```
### Method 2. Lock access and identify the client requester

Register the Guard in app/Providers/AuthServiceProvider.php in the `boot` method.

```php
$this->app['auth']->viaRequest('firebase', function ($request) {
    return app(\sdwru\LaravelFirebaseAuth\Guard::class)->user($request);
});
```

On `config/auth.php` set your api guard driver to 'firebase' and the model to firebase User class

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
            'model' => \sdwru\LaravelFirebaseAuth\User::class,
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
#### Retrieve uid (For method #2 only)

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
