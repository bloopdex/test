<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Here, we don't call the parent's handle method anymore
        // Instead, we directly check if the user is authenticated for any of the guards
        $this->authenticate($request, $guards);

        // If the user is authenticated, proceed with the request
        return $next($request);
    }

    /**
     * Handle unauthenticated users.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        // If the loop completes without finding an authenticated user, throw an exception
        // This is caught by the exception handler and formatted based on your application's needs
        // In this case, we want to always return a JSON response instead of throwing the exception
        $this->unauthenticated($request, $guards);
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        // Here, we directly return a JSON response for unauthenticated users
        ApiResponse::error('Unauthenticated', 'general:unauthenticated', 401)->send();
        exit;
    }

    // Remove redirectTo method since it's not needed anymore
}
