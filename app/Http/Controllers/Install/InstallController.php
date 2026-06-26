<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SettingManager;
// use Database\Seeders\DefaultContentSeeder;
// use Database\Seeders\MenuSeeder;
// use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ProductSeeder;
use DateTimeZone;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
// use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    public function index(): Factory|View
    {
        return view('install.welcome', [
            'step' => 'welcome',
        ]);
    }

    public function requirements(): Factory|View
    {
        $requirements = [
            'PHP version (current: '.PHP_VERSION.') requires 8.2.0+' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'Ctype' => extension_loaded('ctype'),
            'CURL' => extension_loaded('curl'),
            'Fileinfo' => extension_loaded('fileinfo'),
            'GD' => extension_loaded('gd'),
            'JSON' => extension_loaded('json'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),
            'PDO' => extension_loaded('pdo'),
            'Tokenizer' => extension_loaded('tokenizer'),
            'XML' => extension_loaded('xml'),
        ];

        return view('install.requirements', [
            'step' => 'requirements',
            'requirements' => $requirements,
        ]);
    }

    public function permissions(): Factory|View
    {
        $permissionTargets = [
            '.env' => base_path('.env'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $paths = [];

        foreach ($permissionTargets as $label => $path) {
            $paths[$label] = is_writable($path);
        }

        return view('install.permissions', [
            'step' => 'permissions',
            'paths' => $paths,
        ]);
    }

    public function environment(Request $request): Factory|View
    {
        $configuredUrl = config('app.url');
        $host = $configuredUrl ? parse_url($configuredUrl, PHP_URL_HOST) : null;
        $defaultAppUrl = $configuredUrl;

        if (! $defaultAppUrl || in_array($host, ['localhost', '127.0.0.1'], true)) {
            $defaultAppUrl = $request->getSchemeAndHttpHost();
        }

        return view('install.environment', [
            'step' => 'environment',
            'defaults' => [
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug') ? 'true' : 'false',
                'app_url' => $defaultAppUrl,
                'app_timezone' => config('app.timezone'),
                'db_connection' => config('database.default'),
                'db_host' => config('database.connections.mysql.host'),
                'db_port' => config('database.connections.mysql.port'),
                'db_database' => config('database.connections.mysql.database'),
                'db_username' => config('database.connections.mysql.username'),
                'db_password' => config('database.connections.mysql.password'),
            ],
            'timezoneOptions' => DateTimeZone::listIdentifiers(),
        ]);
    }

    public function saveEnvironment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_env' => ['required', 'string', 'in:production,development,local'],
            'app_debug' => ['required', 'string', 'in:true,false'],
            'app_url' => ['required', 'url'],
            'app_timezone' => ['required', 'string', 'timezone'],
            'db_connection' => ['required', 'string', 'max:50'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'string', 'max:10'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        $updates = [
            'APP_NAME' => '"'.$data['app_name'].'"',
            'APP_ENV' => $data['app_env'],
            'APP_DEBUG' => $data['app_debug'],
            'APP_URL' => $data['app_url'],
            'APP_TIMEZONE' => $data['app_timezone'],
            'DB_CONNECTION' => $data['db_connection'],
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ];

        $this->updateEnvironmentFile($updates);
        $this->ensureAppKey();

        $request->session()->put('install.environment_saved', true);
        File::put(storage_path('install_environment_saved'), now()->toDateTimeString());

        return redirect()->route('install.run');
    }

    public function runInstall(): RedirectResponse
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('migrate', ['--force' => true]);

        SettingManager::set('timezone', config('app.timezone', 'Asia/Dhaka'));

        //        Artisan::call('db:seed', [
        //            '--class' => RolePermissionSeeder::class,
        //            '--force' => true,
        //        ]);
        //
        //        Artisan::call('db:seed', [
        //            '--class' => DefaultContentSeeder::class,
        //            '--force' => true,
        //        ]);
        //
        //        Artisan::call('db:seed', [
        //            '--class' => MenuSeeder::class,
        //            '--force' => true,
        //        ]);

        Artisan::call('db:seed', [
            '--class' => ProductSeeder::class,
            '--force' => true,
        ]);

        return redirect()->route('install.account');
    }

    public function account(): Factory|View|RedirectResponse
    {
        if (! $this->environmentIsSaved(request())) {
            return redirect()->route('install.environment');
        }

        return view('install.account', [
            'step' => 'account',
        ]);
    }

    public function storeAccount(Request $request): Factory|View|RedirectResponse
    {
        if (! $this->environmentIsSaved($request)) {
            return redirect()->route('install.environment');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'pf_no' => ['required', 'string', 'max:255'],
            'mobile_no' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        //        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'pf_no' => $data['pf_no'],
            'mobile_no' => $data['mobile_no'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'is_approved' => true,
        ]);

        //        $user->assignRole($role);

        File::put(storage_path('installed'), now()->toDateTimeString());
        File::delete(storage_path('install_environment_saved'));

        Auth::login($user);

        return view('install.final', [
            'step' => 'final',
        ]);
    }

    private function updateEnvironmentFile(array $updates): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            File::put($envPath, '');
        }

        $envContents = File::get($envPath);

        foreach ($updates as $key => $value) {
            $pattern = "/^\\s*(?:export\\s+)?{$key}\\s*=.*$/m";
            $line = $key.'='.$value;

            if (preg_match($pattern, $envContents)) {
                $envContents = preg_replace($pattern, $line, $envContents);

                continue;
            }

            $envContents = rtrim($envContents).PHP_EOL.$line.PHP_EOL;
        }

        File::put($envPath, $envContents);

        foreach ($updates as $key => $value) {
            $cleanValue = Str::of($value)->replace('"', '')->toString();
            putenv($key.'='.$cleanValue);
        }
    }

    private function ensureAppKey(): void
    {
        if ($this->hasEnvironmentKey()) {
            return;
        }

        Artisan::call('key:generate', ['--force' => true]);
    }

    private function hasEnvironmentKey(): bool
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return false;
        }

        $envContents = File::get($envPath);
        $pattern = '/^APP_KEY=(.+)$/m';

        if (preg_match($pattern, $envContents, $matches) !== 1) {
            return false;
        }

        return trim($matches[1]) !== '';
    }

    private function environmentIsSaved(Request $request): bool
    {
        if ($request->session()->get('install.environment_saved')) {
            return true;
        }

        return File::exists(storage_path('install_environment_saved'));
    }
}
