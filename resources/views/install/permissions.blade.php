@extends('install.layout')

@section('title', 'Permissions')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">Folder Permissions</h2>
            <p class="mt-2 text-sm font-medium text-slate-700">Installation system required permissions.</p>
            <p class="mt-2 text-sm text-slate-600">Make sure the following folders are writable by your web server.</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($paths as $path => $status)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <span class="text-slate-700">{{ $path }}</span>
                        <span class="inline-flex items-center gap-2 text-sm font-semibold {{ $status ? 'text-emerald-600' : 'text-rose-600' }}">
                            <i class="fa-solid {{ $status ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                            {{ $status ? 'Writable' : 'Not writable' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="flex flex-wrap gap-3">
            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900" href="{{ route('install.requirements') }}" data-install-loading>Back</a>
            <a class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700" href="{{ route('install.environment') }}" data-install-loading>Next</a>
        </div>
    </div>
@endsection
