<?php
namespace csrui\LaravelFirebaseAuth;

use Kreait\Firebase\Auth;

class Guard
{

    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    
    public function user($request)
    {
        $token = $request->bearerToken();
        try {
            $token = $this->auth->verifyIdToken($token);
            return new User($token->getClaims());
        }
        catch (\Exception $e) {
            return;
        }
    }
}
