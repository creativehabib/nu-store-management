<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // ১. ইউজার লগইন করা থাকলে তার ডাটাবেস প্রেফারেন্স নেবে
        if (Auth::check() && Auth::user()->locale) {
            App::setLocale(Auth::user()->locale);
        }
        // ২. লগইন না থাকলে সেশন থেকে নেবে
        elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }
        // ৩. ডিফল্ট কনফিগারেশন
        else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
