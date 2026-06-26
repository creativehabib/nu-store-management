<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Install') | {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('style')
</head>
<body class="h-screen w-screen bg-gray-100 font-sans antialiased text-gray-800">
<div class="mx-auto max-w-6xl mt-6 bg-gray-100 font-sans antialiased">
    <h1 class="text-center items-center font-bold text-2xl mb-5">Installation</h1>
    <div class="md:flex">
        <aside class="relative bg-[#f0ebf8] p-6">
            @php
                $steps = [
                    'welcome' => 'Welcome',
                    'requirements' => 'Server Requirements',
                    'environment' => 'Environment Settings',
                    'account' => 'Create account',
                    'license' => 'Activate License',
                    'final' => 'Done',
                ];
                $activeStep = $step === 'permissions' ? 'requirements' : $step;
            @endphp
            <div class="relative z-10 flex flex-col space-y-5">
                @foreach ($steps as $key => $label)
                    @php
                        $isActive = $activeStep === $key;
                        $isDone = array_search($activeStep, array_keys($steps), true) > array_search($key, array_keys($steps), true);
                        $circleClasses = $isActive
                            ? 'bg-blue-700 text-white'
                            : ($isDone ? 'bg-blue-100 text-blue-700' : 'bg-gray-300 text-gray-500');
                        $labelClasses = $isActive
                            ? 'text-gray-800 font-semibold'
                            : ($isDone ? 'text-gray-700 font-medium' : 'text-gray-400 font-medium');
                    @endphp
                    <div class="flex items-center space-x-4 {{ $isActive ? '' : 'opacity-80' }}">
                        <div class="z-20 flex h-9 w-9 items-center justify-center rounded-full {{ $circleClasses }} font-bold">
                            {{ array_search($key, array_keys($steps), true) + 1 }}
                        </div>
                        <span class="text-lg {{ $labelClasses }}">{{ $label }}</span>
                    </div>
                @endforeach
                <div class="absolute left-4 top-4 -z-10 h-[85%] w-0.5 bg-gray-300"></div>
            </div>
        </aside>
        <main class="flex flex-1 flex-col bg-white">
            <div class="flex-1 p-8">
                @yield('content')
            </div>
            @hasSection('footer')
                <footer class="border-t border-gray-200 p-6">
                    @yield('footer')
                </footer>
            @endif
        </main>
    </div>
</div>
<div id="install-loading" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm" aria-hidden="true">
    <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-lg">
        <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <span>Loading, please wait...</span>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const overlay = document.getElementById('install-loading');
        if (!overlay) {
            return;
        }

        const showLoading = () => {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            overlay.setAttribute('aria-hidden', 'false');
        };

        document.querySelectorAll('[data-install-loading]').forEach((element) => {
            const isForm = element.tagName === 'FORM';
            const eventName = isForm ? 'submit' : 'click';
            element.addEventListener(eventName, () => {
                if (!isForm) {
                    const href = element.getAttribute('href');
                    if (href && href.startsWith('#')) {
                        return;
                    }
                }
                showLoading();
            });
        });
    });
</script>
</body>
</html>
