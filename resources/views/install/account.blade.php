@extends('install.layout')

@section('title', 'Create Account')

@section('content')
    <div class="space-y-8">
        <div class="rounded-lg border border-slate-200/70 bg-white/80 p-6 shadow-sm backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Installation step</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Create Administrator Account</h2>
                    <p class="mt-2 text-sm text-slate-600">Create the first administrator user. You will use this account to log in and manage system settings.</p>
                </div>
                <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                    <p class="font-semibold">Security tip</p>
                    <p class="mt-1 text-xs text-blue-600">Use a strong password with at least 8 characters.</p>
                </div>
            </div>
        </div>

        <form class="space-y-6 rounded-lg border border-slate-200/70 bg-white p-6 shadow-sm" method="post" action="{{ route('install.account.store') }}" data-install-loading>
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Account details</h3>
                    <p class="mt-1 text-sm text-slate-600">Fill in the information for the primary administrator account.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Required fields</span>
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="name">Full name</label>
                    <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter full name" required>
                    @error('name')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="pf_no">PF No</label>
                    <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="pf_no" name="pf_no" type="number" value="{{ old('pf_no') }}" placeholder="Exa. Ex: 2125" required>
                    @error('pf_no')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="mobile_no">Mobile No</label>
                    <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="mobile_no" name="mobile_no" type="number" value="{{ old('mobile_no') }}" placeholder="Exa. Ex: 01912134334" required>
                    @error('mobile_no')
                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="email">Email address</label>
                    <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="example@gmail.com" required>
                    @error('email')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="password">Password</label>
                    <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="password" name="password" type="password" placeholder="******" required>
                    @error('password')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="password_confirmation">Confirm password</label>
                    <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="password_confirmation" name="password_confirmation" type="password" placeholder="******" required>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-5 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900" href="{{ route('install.environment') }}" data-install-loading>Back</a>
                <button class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700" type="submit">Create Account</button>
            </div>
        </form>
    </div>
@endsection
