<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Flux\Flux;

class CacheManagement extends Component
{
    public $cacheSize = '0 B';

    public function mount()
    {
        $this->updateCacheSize();
    }

    /**
     * Update the calculated cache and log size.
     */
    public function updateCacheSize()
    {
        $size = 0;

        // Cache directory size
        $cachePath = storage_path('framework/cache/data');
        if (File::exists($cachePath)) {
            foreach (File::allFiles($cachePath) as $file) {
                $size += $file->getSize();
            }
        }

        // Log directory size
        $logPath = storage_path('logs');
        if (File::exists($logPath)) {
            foreach (File::allFiles($logPath) as $file) {
                $size += $file->getSize();
            }
        }

        $this->cacheSize = $this->formatBytes($size);
    }

    /**
     * Format bytes into a human-readable string.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << ($pow * 10));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    // 1. Clear All CMS Cache
    public function clearAllCache()
    {
        Artisan::call('cache:clear');
        $this->updateCacheSize();
        Flux::toast(__('Application cache cleared successfully!'));
    }

    // 2. Clear Compiled Views
    public function clearCompiledViews()
    {
        Artisan::call('view:clear');
        $this->updateCacheSize();
        Flux::toast(__('Compiled views cleared successfully!'));
    }

    // 3. Clear Config Cache
    public function clearConfigCache()
    {
        Artisan::call('config:clear');
        $this->updateCacheSize();
        Flux::toast(__('Configuration cache cleared successfully!'));
    }

    // 4. Clear Route Cache
    public function clearRouteCache()
    {
        Artisan::call('route:clear');
        $this->updateCacheSize();
        Flux::toast(__('Route cache cleared successfully!'));
    }

    // 5. Clear Log Files
    public function clearLogFiles()
    {
        $logPath = storage_path('logs');
        if (File::exists($logPath)) {
            $files = File::allFiles($logPath);
            foreach ($files as $file) {
                File::delete($file->getRealPath());
            }
        }

        $this->updateCacheSize();
        Flux::toast(__('Log files deleted successfully!'));
    }

    // 6. Clear Optimization Cache
    public function clearOptimizationCaches()
    {
        Artisan::call('optimize:clear');
        $this->updateCacheSize();
        Flux::toast(__('Optimization caches cleared successfully!'));
    }

    // 7. Cache Views
    public function cacheViews()
    {
        Artisan::call('view:cache');
        $this->updateCacheSize();
        Flux::toast(__('Blade views cached successfully!'));
    }

    public function render()
    {
        return view('livewire.admin.cache-management');
    }
}
