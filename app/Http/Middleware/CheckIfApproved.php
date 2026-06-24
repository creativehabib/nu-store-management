<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // চেক করা হচ্ছে ইউজার লগইন করা আছে কিনা এবং তার is_approved স্ট্যাটাস false কিনা
        if (Auth::check() && !Auth::user()->is_approved) {

            // ইউজারকে লগআউট করা
            Auth::logout();

            // সেশন ইনভ্যালিডেট করে দেওয়া (সিকিউরিটির জন্য)
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // লগইন পেজে রিডাইরেক্ট করা এবং একটি মেসেজ পাঠানো
            return redirect()->route('login')->with('status', 'আপনার অ্যাকাউন্টটি এখনও এডমিন কর্তৃক অনুমোদিত হয়নি। অনুগ্রহ করে অনুমোদনের জন্য অপেক্ষা করুন।');
        }

        return $next($request);
    }
}
