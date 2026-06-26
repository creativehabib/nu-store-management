@extends('install.layout')

@section('title', 'Installation Complete')

@section('content')
    <div class="space-y-8">
        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-6 py-8 text-center shadow-sm sm:px-10">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl text-emerald-600 shadow">
                <i class="fa-solid fa-circle-check"></i>
            </span>
            <h2 class="mt-4 text-2xl font-semibold text-emerald-900">Installation Complete</h2>
            <p class="mt-2 text-sm text-emerald-700">Your application has been installed successfully. You're now signed in.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-3">
            <a class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700" href="{{ route('dashboard') }}" data-install-loading>Go to Dashboard</a>
            <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900" href="{{ route('home') }}" data-install-loading>Go to Homepage</a>
        </div>
    </div>
@endsection
