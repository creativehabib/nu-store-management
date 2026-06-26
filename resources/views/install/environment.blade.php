@extends('install.layout')

@section('title', 'Environment')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Environment Settings</h2>
                <p class="mt-2 text-sm text-slate-600">Set up your application identity and database connectivity.</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 shadow-sm">
                <p class="font-semibold text-slate-900">Need a quick start?</p>
                <p class="mt-1">Use default values now, then update later in <span class="font-semibold text-slate-800">.env</span>.</p>
            </div>
        </div>

        <form class="space-y-8" method="post" action="{{ route('install.environment.save') }}" data-install-loading>
            @csrf
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10 2a6 6 0 00-6 6v1.5a2.5 2.5 0 01-.732 1.768l-.518.518A1 1 0 004 13h12a1 1 0 00.707-1.707l-.518-.518A2.5 2.5 0 0115 9.5V8a6 6 0 00-6-6zm-3 14a3 3 0 006 0H7z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Application</h3>
                        <p class="text-sm text-slate-600">Customize the branding and base URL for your app.</p>
                    </div>
                </div>
                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="app_name">Application name</label>
                        <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="app_name" name="app_name" type="text" value="{{ old('app_name', $defaults['app_name']) }}" required>
                        <p class="mt-2 text-xs text-slate-500">Shown in emails, titles, and navigation.</p>
                        @error('app_name')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="app_env">Environment</label>
                        <select class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="app_env" name="app_env" required>
                            @foreach (['production' => 'Production', 'development' => 'Development', 'local' => 'Local'] as $env => $label)
                                <option value="{{ $env }}" @selected(old('app_env', $defaults['app_env']) === $env)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Select how this installation should behave.</p>
                        @error('app_env')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="app_debug">Debug mode</label>
                        <select class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="app_debug" name="app_debug" required>
                            @foreach (['false' => 'Off', 'true' => 'On'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('app_debug', $defaults['app_debug']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Keep debug off in production.</p>
                        @error('app_debug')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="app_url">Application URL</label>
                        <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="app_url" name="app_url" type="url" value="{{ old('app_url', $defaults['app_url']) }}" required>
                        <p class="mt-2 text-xs text-slate-500">Include the full protocol (https://).</p>
                        @error('app_url')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="app_timezone">Time zone</label>
                        <select class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="app_timezone" name="app_timezone" required>
                            @foreach ($timezoneOptions as $timezone)
                                <option value="{{ $timezone }}" @selected(old('app_timezone', $defaults['app_timezone']) === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Choose the default time zone for dates and times.</p>
                        @error('app_timezone')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M4 4a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 2a1 1 0 000 2h4a1 1 0 100-2H7zm0 4a1 1 0 000 2h4a1 1 0 100-2H7z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Database</h3>
                        <p class="text-sm text-slate-600">Choose your driver and provide the connection credentials.</p>
                    </div>
                </div>
                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="db_connection">Database driver</label>
                        <select class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="db_connection" name="db_connection" required>
                            @foreach (['mysql' => 'MySQL', 'pgsql' => 'PostgreSQL', 'sqlite' => 'SQLite', 'sqlsrv' => 'SQL Server'] as $driver => $label)
                                <option value="{{ $driver }}" @selected(old('db_connection', $defaults['db_connection']) === $driver)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">SQLite stores data in a local file.</p>
                        @error('db_connection')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="db_host">Database host</label>
                        <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="db_host" name="db_host" type="text" value="{{ old('db_host', $defaults['db_host']) }}" required>
                        <p class="mt-2 text-xs text-slate-500">For Laravel Sail, use <span class="font-semibold text-slate-700">mysql</span>.</p>
                        @error('db_host')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="db_port">Database port</label>
                        <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="db_port" name="db_port" type="text" value="{{ old('db_port', $defaults['db_port']) }}" required>
                        @error('db_port')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="db_database">Database name</label>
                        <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="db_database" name="db_database" type="text" value="{{ old('db_database', $defaults['db_database']) }}" required>
                        <p class="mt-2 text-xs text-slate-500" id="sqlite_hint">For SQLite, enter a full path like <span class="font-semibold text-slate-700">database/database.sqlite</span>.</p>
                        @error('db_database')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="db_username">Database username</label>
                        <input class="mt-2 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="db_username" name="db_username" type="text" value="{{ old('db_username', $defaults['db_username']) }}" required>
                        @error('db_username')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="db_password">Database password</label>
                        <div class="relative mt-2">
                            <input class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 pr-12 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200" id="db_password" name="db_password" type="password" value="{{ old('db_password', $defaults['db_password']) }}">
                            <button class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 transition hover:text-slate-700" type="button" data-password-toggle>
                                Show
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Leave blank if your database has no password.</p>
                        @error('db_password')
                            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-5 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900" href="{{ route('install.permissions') }}" data-install-loading>Back</a>
                <button class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700" type="submit">Save &amp; Install</button>
            </div>
        </form>
    </div>

    <script>
        const dbConnection = document.getElementById('db_connection');
        const sqliteHint = document.getElementById('sqlite_hint');
        const toggleFields = () => {
            const isSqlite = dbConnection.value === 'sqlite';
            const fields = ['db_host', 'db_port', 'db_username'];
            fields.forEach((fieldId) => {
                const field = document.getElementById(fieldId);
                if (!field) {
                    return;
                }
                field.disabled = isSqlite;
                field.required = !isSqlite;
                field.classList.toggle('bg-slate-100', isSqlite);
                field.classList.toggle('text-slate-500', isSqlite);
            });
            if (sqliteHint) {
                sqliteHint.classList.toggle('text-slate-500', isSqlite);
                sqliteHint.classList.toggle('text-slate-400', !isSqlite);
            }
        };

        const appUrlInput = document.getElementById('app_url');
        if (appUrlInput && !appUrlInput.value) {
            appUrlInput.value = window.location.origin;
        }

        const passwordToggle = document.querySelector('[data-password-toggle]');
        const passwordInput = document.getElementById('db_password');
        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                passwordToggle.textContent = isPassword ? 'Hide' : 'Show';
            });
        }

        if (dbConnection) {
            dbConnection.addEventListener('change', toggleFields);
            toggleFields();
        }

        const initTimezoneChoices = () => {
            const timezoneSelect = document.getElementById('app_timezone');
            if (!timezoneSelect || timezoneSelect.dataset.choicesInitialized === 'true' || !window.Choices) {
                return false;
            }

            new window.Choices(timezoneSelect, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: '',
                placeholderValue: 'Select a time zone',
            });

            timezoneSelect.dataset.choicesInitialized = 'true';

            return true;
        };

        if (!initTimezoneChoices()) {
            window.addEventListener('load', initTimezoneChoices, { once: true });
        }
    </script>
@endsection
