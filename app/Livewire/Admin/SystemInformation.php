<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SystemInformation extends Component
{
    public array $systemEnvironment = [];
    public array $serverEnvironment = [];
    public array $databaseInformation = [];
    public array $phpConfiguration = [];

    public function mount()
    {
        $connection = config('database.default');
        $connectionConfig = config("database.connections.{$connection}", []);

        $this->systemEnvironment = [
            'Application Environment' => app()->environment(),
            'App Debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            'App URL' => config('app.url'),
            'Timezone' => config('app.timezone'),
            'Locale' => config('app.locale'),
            'Framework Version' => app()->version(),
        ];

        $this->serverEnvironment = [
            'Server Software' => request()->server('SERVER_SOFTWARE') ?? 'N/A',
            'Server OS' => trim(php_uname('s') . ' ' . php_uname('r')),
            'PHP SAPI' => PHP_SAPI,
            'Host' => request()->getHost(),
            'Server IP' => request()->server('SERVER_ADDR') ?? 'N/A',
        ];

        // Database Information Logic
        $databaseVersion = 'N/A';
        $maxConnections = 'N/A';
        try {
            $databaseVersion = DB::selectOne('select version() as version')->version ?? 'N/A';
            $driver = $connectionConfig['driver'] ?? null;
            if ($driver === 'mysql') {
                $maxConnections = DB::selectOne('select @@max_connections as max_connections')->max_connections ?? 'N/A';
            } elseif ($driver === 'pgsql') {
                $maxConnections = DB::selectOne('show max_connections')->max_connections ?? 'N/A';
            }
        } catch (\Throwable $exception) {
            $databaseVersion = 'N/A';
        }

        $this->databaseInformation = [
            'Connection' => $connection,
            'Driver' => $connectionConfig['driver'] ?? 'N/A',
            'Host' => $connectionConfig['host'] ?? 'N/A',
            'Port' => $connectionConfig['port'] ?? 'N/A',
            'Database' => $connectionConfig['database'] ?? 'N/A',
            'Database Version' => $databaseVersion,
            'Character Set' => $connectionConfig['charset'] ?? 'N/A',
            'Collation' => $connectionConfig['collation'] ?? 'N/A',
            'Max Connections' => $maxConnections,
        ];

        $this->phpConfiguration = [
            'PHP Version' => PHP_VERSION,
            'Memory Limit' => ini_get('memory_limit'),
            'Max Execution Time' => ini_get('max_execution_time') . 's',
            'Max Input Time' => ini_get('max_input_time') . 's',
            'Max Input Vars' => ini_get('max_input_vars'),
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size' => ini_get('post_max_size'),
            'Max File Uploads' => ini_get('max_file_uploads'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.system-information');
    }
}
