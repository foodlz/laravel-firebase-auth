# laravel-firebase-auth
Secure your laravel API with Google Firebase Auth

Adding the *Middleware* to your API will ensure that access is granted only using a valid Bearer Token issues by Goggle Firebase Auth.

The main difference between this package and the package we forked it from is that we are using [laravel-firebase](https://github.com/kreait/laravel-firebase) as a dependency which in turn depends on [firebase-php](https://github.com/kreait/firebase-php).  Using that package instead of firebase-tokens (which is already included in firebase-php) removes the need for a service provider in this package since it is already included in laravel-firebase.  Since that package depends on [firebase-php](https://github.com/kreait/firebase-php), you also can use [all the features that package provides](https://github.com/kreait/firebase-php#documentation).

#### Role middleware
This package includes optional role middleware for more granular access.

## Install
```bash
composer require sdwru/laravel-firebase-auth
```
#### laravel-firebase
Publish the laravel-firebase ServiceProvider (`Provider: Kreait\Laravel\Firebase\ServiceProvider`) if not already done so.

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
 
```
\sdwru\LaravelFirebaseAuth\Middleware\JWTAuth::class,
```
Refer to the [Laravel Middleware documentation](https://laravel.com/docs/7.x/middleware) on where you can put this in your Kernel.php file and how it can be used in routes.
### Method 2. Lock access and identify the client requester

Add the Guard to `app/Providers/AuthServiceProvider.php` in the `boot` method.

```php
public function boot()
{
   $this->registerPolicies();

   $this->app['auth']->viaRequest('firebase', function ($request) {
       return app(\sdwru\LaravelFirebaseAuth\Guard::class)->user($request);
   });
}
```

In `config/auth.php` set your api guard driver to `firebase` and the model to `LaravelFirebaseAuth\User::class`

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
#### Example: Retrieve uid (For method #2 only)

```php
<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

class UserController extends Controller
{
    public function foo(Request $request, Guard $guard)
    {
        
        // Retrieve Firebase uid from id token via request
        $user = $request->user();
        $uid = $user->getAuthIdentifier()
        
        // Or, do the same thing using guard instead
        $user = $guard->user();
        $uid = $user->getAuthIdentifier();
        
        
        // Do something with the request for this user
    }
}
```
### Role Middleware
To use this optional feature add the following to `app/Http/Kernel.php`.
```php
protected $routeMiddleware = [

...
...

'role' => \sdwru\LaravelFirebaseAuth\Middleware\Role::class,

];
```
#### Add role to user example
```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Kreait\Firebase\Auth;

class UserController extends Controller
{
  public $auth;
  
  public function __construct(Auth $auth)
  {
      $this->auth = $auth;
  }
   
  public function index(Request $request)
  {
      $users = $this->auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
 
      foreach ($users as $k => $v) {
          $response[$k] = $v;
      }
      echo json_encode($response);
   }
   
   public function update(Request $request, $uid)
   {   
       $this->validate($request, [
           'role' => 'present|string|max:20',
       ]);
       
       $customAttributes = [
         'role' => $request->role,
       ];
       
       $updatedUser = $this->auth->setCustomUserAttributes($uid, $customAttributes);
       
       return $this->auth->getUser($uid);
   }
}
```
##### Routes
After assigning roles, add them to `routes/api.php`.

```php
// Allow any authenticated user
Route::middleware('auth:api')->apiResource('users', 'API\UserController');

// Only allow users with admin and foo roles
Route::middleware('auth:api', role:admin, foo')->apiResource('users', 'API\FooController');

// Allow users with admin role only
Route::middleware('auth:api', 'role:admin')->apiResource('users', 'API\AdminController');
```
The firebase-php sdk refers to the property where we assign roles as custom "attributes". Firebase and JWT refers to them as custom "claims".  The important thing to understand is they are referring to the the same thing.

##### Role references

https://firebase.google.com/docs/auth/admin/custom-claims

https://firebase.google.com/docs/firestore/solutions/role-based-access

https://firebase-php.readthedocs.io/en/5.3.0/user-management.html?highlight=setCustomUserAttributes#update-a-user

https://www.toptal.com/firebase/role-based-firebase-authentication


## Support

Feel free to open issues and provide feedback.
