<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Crypt;
use App\Models\Teacher;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = $request->path();
        if (in_array($url, ['oauth/login', 'login'])) {
            return $next($request);
        }

        $token = $request->input('token');
        if (!$token) {
            return [
                'state' => -1,
                'message' => '未登录',
            ];
        }
        $decrypted = Crypt::decrypt($token);

        $decrypted = json_decode($decrypted, true);
        return $next($request);
    }
}
