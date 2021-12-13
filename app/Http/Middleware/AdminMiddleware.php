<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\token;
use Illuminate\Http\Request;

class AdminMiddleware
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
        $token = $request->bearerToken();
        if (empty($token)) {
            return response([
                'message' => 'Token Invalid',
            ]);
        } else {
            $decode      = JWT::decode($token, new Key('My_Key', 'HS256'));
            $token_Exist = token::where('user_id', $decode->data)->first();
            if (!isset($token_Exist)) {
                return response([
                    'message' => 'Unauthenticated',
                ]);
            } else {
                return $next($request);
            }
        }
    }
}
