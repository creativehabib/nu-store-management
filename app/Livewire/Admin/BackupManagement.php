<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\Process\Process;

class BackupManagement extends Component
{
    public $backupToDelete = null;

    private string $backupFolder = 'backups/database';

    public function generateBackup(): void
    {
        [$connection, $config] = $this->databaseConfig();

        if (! $config) {
            Flux::toast('Database connection is not configured.', variant: 'danger');

            return;
        }

        File::ensureDirectoryExists($this->backupDirectory());

        $filename = sprintf(
            '%s-%s.sql',
            Str::slug(config('app.name', 'laravel')),
            now()->format('Ymd_His')
        );

        $fullPath = $this->backupDirectory().DIRECTORY_SEPARATOR.$filename;

        $process = $this->buildBackupProcess($config, $fullPath);

        if (! $process) {
            return;
        }

        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            Flux::toast('Backup failed: '.trim($process->getErrorOutput()), variant: 'danger');

            return;
        }

        Flux::toast('Backup created successfully!');
    }

    public function restoreBackup(string $backup): void
    {
        $backupPath = $this->backupPath($backup);

        if (! File::exists($backupPath)) {
            Flux::toast('Backup file not found.', variant: 'danger');

            return;
        }

        [, $config] = $this->databaseConfig();

        if (! $config) {
            Flux::toast('Database connection is not configured.', variant: 'danger');

            return;
        }

        $process = $this->buildRestoreProcess($config, $backupPath);

        if (! $process) {
            return;
        }

        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            Flux::toast('Restore failed: '.trim($process->getErrorOutput()), variant: 'danger');

            return;
        }

        Flux::toast('Backup restored successfully!');
    }

    public function downloadBackup(string $backup)
    {
        $backupPath = $this->backupPath($backup);

        if (! File::exists($backupPath)) {
            Flux::toast('Backup file not found.', variant: 'danger');

            return null;
        }

        return response()->download($backupPath);
    }

    public function confirmDelete(string $filename): void
    {
        $this->backupToDelete = $filename;
        Flux::modal('delete-backup-modal')->show();
    }

    public function executeDelete(): void
    {
        if (! $this->backupToDelete) {
            return;
        }

        $backupPath = $this->backupPath($this->backupToDelete);

        if (! File::exists($backupPath)) {
            Flux::toast('Backup file not found.', variant: 'danger');
            return;
        }

        File::delete($backupPath);

        $this->backupToDelete = null;
        Flux::modal('delete-backup-modal')->close();

        Flux::toast('Backup deleted successfully!');
    }

    public function render()
    {
        return view('livewire.admin.backup-management', [
            'backups' => $this->listBackups(),
        ])->layout('layouts.app', ['title' => 'Database Backups']);
    }

    private function backupDirectory(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.$this->backupFolder);
    }

    private function backupPath(string $backup): string
    {
        return $this->backupDirectory().DIRECTORY_SEPARATOR.basename($backup);
    }

    private function listBackups(): array
    {
        if (! File::exists($this->backupDirectory())) {
            return [];
        }

        $files = File::files($this->backupDirectory());

        return collect($files)
            ->filter(fn (\SplFileInfo $file) => $file->isFile() && str_ends_with($file->getFilename(), '.sql'))
            ->map(function (\SplFileInfo $file) {
                return [
                    'name' => $file->getFilename(),
                    'description' => 'Database dump',
                    'size' => $this->readableSize($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d h:i A'),
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    private function readableSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return sprintf('%.2f %s', $size, $units[$index]);
    }

    private function databaseConfig(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        return [$connection, $config];
    }

    private function buildBackupProcess(array $config, string $fullPath): ?Process
    {
        $driver = $config['driver'] ?? '';

        if ($driver === 'mysql') {
            $process = new Process([
                'mysqldump',
                '--user='.($config['username'] ?? 'root'),
                '--host='.($config['host'] ?? '127.0.0.1'),
                '--port='.($config['port'] ?? 3306),
                '--databases',
                $config['database'] ?? '',
                '--result-file='.$fullPath,
            ]);

            if (! empty($config['password'])) {
                $process->setEnv(['MYSQL_PWD' => $config['password']]);
            }

            return $process;
        }

        Flux::toast('Unsupported database driver for backup.', variant: 'danger');

        return null;
    }

    private function buildRestoreProcess(array $config, string $backupPath): ?Process
    {
        $driver = $config['driver'] ?? '';

        if ($driver === 'mysql') {
            $process = new Process([
                'mysql',
                '--user='.($config['username'] ?? 'root'),
                '--host='.($config['host'] ?? '127.0.0.1'),
                '--port='.($config['port'] ?? 3306),
                $config['database'] ?? '',
            ]);

            if (! empty($config['password'])) {
                $process->setEnv(['MYSQL_PWD' => $config['password']]);
            }

            $process->setInput(File::get($backupPath));

            return $process;
        }

        Flux::toast('Unsupported database driver for restore.', variant: 'danger');

        return null;
    }
}
