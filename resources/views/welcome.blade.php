<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ setting('site_name', 'Store Management System') }} | {{ __('National University') }}</title>
    <link rel="icon" href="/logo.png" sizes="any">

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
    <script>
        tailwind.config={darkMode:'class'};
        if(localStorage.theme==='dark'){document.documentElement.classList.add('dark')}
        function toggleTheme(){
            document.documentElement.classList.toggle('dark');
            localStorage.theme=document.documentElement.classList.contains('dark')?'dark':'light';
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 transition-colors duration-300">
    <div class=" border-b border-zinc-200 dark:border-zinc-800">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="/"><img src="{{  asset('logo.png') }}" class="w-8 h-10" alt="Logo"></a>
                <div>
                    <h2 class="font-bold text-lg leading-tight">National University</h2>
                    <span class="text-xs text-zinc-500 uppercase tracking-widest">Bangladesh</span>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-zinc-200 dark:hover:bg-zinc-800 transition">
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
                <div class="relative" x-data="{ open: false }">
                    @auth
                        <button @click="open = !open" @click.away="open = false"
                                class="flex items-center gap-2 text-sm font-semibold hover:text-indigo-600 transition">
                            <span>{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open"
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl overflow-hidden z-50">
                            <a href="{{ url('/dashboard') }}" class="block px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-zinc-100 dark:hover:bg-zinc-700">Logout</button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold hover:text-indigo-600 transition">Login</a>
                    @endauth
                </div>
            </div>
        </nav>
    </div>

    <main class="max-w-7xl mx-auto px-8 py-20">
        <div class="grid md:grid-cols-2 gap-16 items-center">
            <div>
                <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight tracking-tight">
                    Store Management <br/><span class="text-indigo-600 dark:text-indigo-400">System</span>
                </h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed">
                    A centralized digital portal for the National University's stationery management, procurement tracking, and requisition approvals.
                </p>
                <div class="flex gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-semibold shadow-lg shadow-indigo-600/30 transition">Access Portal</a>
                    @else
                        <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-semibold transition">Official Login</a>
                        <a href="{{ route('register') }}" class="border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-900 px-8 py-3 rounded-lg font-semibold transition">Register Account</a>
                    @endauth
                </div>
            </div>
            <div class="h-[400px] rounded-3xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-2xl">
                <img src="https://thumbs.dreamstime.com/b/paper-words-inventory-management-charts-92977969.jpg"
                     alt="Inventory Management"
                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
            </div>
        </div>
    </main>

    <footer class="text-center py-10 border-t border-zinc-200 dark:border-zinc-800 text-zinc-500 text-sm">
        <p>&copy; {{ date('Y') }} National University, Bangladesh. All rights reserved.</p>
        <p class="mt-2 text-xs opacity-75"><span>{{ __('Developed by') }}</span>
            <span class="font-semibold text-zinc-800 dark:text-zinc-300">Habibur Rahaman</span></p>
    </footer>
</body>
</html>
