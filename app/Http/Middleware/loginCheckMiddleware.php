<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\token;
use App\Models\User;
use Illuminate\Http\Request;
//Password_Security
use Illuminate\Support\Facades\Hash;

class loginCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // DB_Query
        $user = User::where('email', $request->email)->first();
        // Check_User_Existance
        if (!$user) {
            return response([
                'message' => 'User not Registered',
                'status' => '401',
            ]);
        } elseif ($request->email != $user->email) {
            return response([
                'message' => 'Incorrect Email',
                'status' => '401',
            ]);
        } elseif (!Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Incorrect Password',
                'status' => '401',
            ]);
        } elseif ($user->email_verified_at == null) {
            return response([
                'message' => 'Please Verify Your Email',
            ]);
        } else {
            return $next($request);
        }
    }
}
