<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckIfInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // যদি 'installed' ফাইলটি না থাকে এবং ইউজার ইনস্টল পেজে না থাকে
        if (! is_installed() && ! $request->is('install*')) {
            return redirect()->route('install.index');
        }

        // ইনস্টল করা থাকলে সাধারণত ইনস্টল পেজে যেতে দেওয়া হবে না
        if (is_installed() && $request->is('install*')) {
            if ($this->databaseIsAvailable()) {
                return redirect('/');
            }
        }

        if (is_installed() && ! $request->is('install*') && ! $this->databaseIsAvailable()) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }

    private function databaseIsAvailable(): bool
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }
}
