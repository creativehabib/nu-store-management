<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('Store Management System | National University') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 antialiased min-h-screen flex flex-col items-center justify-center selection:bg-indigo-500 selection:text-white">

<div class="w-full max-w-md px-6">
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">

        <div class="p-8 sm:p-10 text-center space-y-6">
            <div class="flex justify-center">
                <div class="w-24 h-24 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center border-4 border-white dark:border-zinc-800 shadow-sm overflow-hidden">
                    <img src="{{ asset('logo.png') }}" alt="NU Logo" class="w-20 h-20 object-contain" onerror="this.outerHTML='<svg class=\'w-12 h-12 text-zinc-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z\'></path></svg>'">
                </div>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">{{ __('National University, Bangladesh') }}</h1>
                <h2 class="text-lg font-medium text-indigo-600 dark:text-indigo-400">{{ __('Store Management System') }}</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                    {{ __('Digital portal for official goods and stationery requisition and inventory management.') }}
                </p>
            </div>

            <div class="pt-4 space-y-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="flex items-center justify-center w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors duration-200 shadow-sm">
                            {{ __('Enter Dashboard') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center justify-center w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors duration-200 shadow-sm">
                            {{ __('Login to System') }}
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="flex items-center justify-center w-full py-2.5 px-4 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-semibold rounded-lg transition-colors duration-200 shadow-sm">
                                {{ __('New Requisitioner Account') }}
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>

        <div class="bg-zinc-50 dark:bg-zinc-900/50 p-4 border-t border-zinc-200 dark:border-zinc-700 text-center">
            <p class="text-xs text-zinc-500 dark:text-zinc-500">
                &copy; {{ date('Y') }} {{ __('National University') }}. All rights reserved.
            </p>
        </div>
    </div>
</div>

</body>
</html>
