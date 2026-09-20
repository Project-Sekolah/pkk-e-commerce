<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/')->with('alert', [
                'type' => 'error',
                'message' => 'Silakan login terlebih dahulu.',
            ]);
        }

        $user = Auth::user();

        if ($user->is_blocked) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('alert', [
                'type' => 'error',
                'message' => 'Akun Anda telah diblokir. Silakan hubungi admin.',
            ]);
        }

        if (!in_array($user->role, $roles)) {
            return redirect('/')->with('alert', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
            ]);
        }

        return $next($request);
    }
}