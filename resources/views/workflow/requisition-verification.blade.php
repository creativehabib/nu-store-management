<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Requisition Verification') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <main class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-3xl bg-white dark:bg-zinc-900 rounded-xl shadow border border-zinc-200 dark:border-zinc-700 p-6 space-y-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ __('Requisition Verification') }}</h1>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('Scan result from official requisition QR code.') }}</p>
                </div>

                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                    {{ __('Verified Link') }}
                </span>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Requisition No') }}</dt>
                    <dd class="mt-1">{{ $requisition->requisition_no }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Current Status') }}</dt>
                    <dd class="mt-1">{{ ucwords(str_replace('_', ' ', $requisition->status)) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Applicant') }}</dt>
                    <dd class="mt-1">{{ $requisition->user->name }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Department') }}</dt>
                    <dd class="mt-1">{{ $requisition->user->department->name ?? 'N/A' }}</dd>
                </div>
            </dl>

            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <p class="text-xs text-zinc-500">{{ __('This status is shown from the live system. If the QR link is tampered with, verification will fail.') }}</p>
            </div>
        </div>
    </main>
</body>
</html>
