<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Crypt;
use App\Models\Teacher;

class AdminAuth
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

        $token = $request->input('token');
        if (!$token) {
            return [
                'state' => -1,
                'message' => '未登录',
            ];
        }
        $decrypted = Crypt::decrypt($token);
        $decrypted = json_decode($decrypted, true);
        if (!key_exists('time', $decrypted)) {
            return [
                'state' => -1,
                'message' => 'token 错误',
            ];
        }
        if ($decrypted['time'] < time() - (60*3*60) ) {
            return [
                'state' => -1,
                'message' => 'token 错误',
            ];
        }

        return $next($request);
    }
}
