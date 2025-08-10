<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthExternal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $username = $request->getUser();
        $password = $request->getPassword();
        if (!$username || !$password) {
            throw new HttpResponseException(
                response()->json(['message' => 'Credenciais não informadas'], 401, [
                    'WWW-Authenticate' => 'Basic realm="API Access"'
                ])
            );
        }
        
        if ($username === env('basic_auth_username') && $password === env('basic_auth_password')) {
            return $next($request);
        }

        return response('Acesso negado', 401, ['WWW-Authenticate' => 'Basic']);
    }
}
