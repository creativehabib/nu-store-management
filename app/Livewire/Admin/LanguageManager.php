<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Flux\Flux;

class LanguageManager extends Component
{
    public $locale = 'bn';
    public $search = '';

    public $baseTranslations = [];
    public $translations = [];

    // নতুন শব্দ যুক্ত করার প্রোপার্টি
    public $newKey = '';
    public $newValue = '';

    // পপআপ/মডালে এডিট করার প্রোপার্টি
    public $editingKey = null;
    public $editEnValue = '';
    public $editTargetValue = '';

    // পরিসংখ্যানের জন্য প্রোপার্টি
    public $totalKeys = 0;
    public $translatedKeys = 0;
    public $missingKeys = 0;

    public function mount()
    {
        $this->loadTranslations();
    }

    public function updatedLocale()
    {
        $this->loadTranslations();
    }

    public function loadTranslations()
    {
        $basePath = lang_path('en.json');
        if (File::exists($basePath)) {
            $this->baseTranslations = json_decode(File::get($basePath), true) ?? [];
        } else {
            File::put($basePath, json_encode([], JSON_PRETTY_PRINT));
            $this->baseTranslations = [];
        }

        $targetPath = lang_path($this->locale . '.json');
        if (File::exists($targetPath)) {
            $this->translations = json_decode(File::get($targetPath), true) ?? [];
        } else {
            File::put($targetPath, json_encode([], JSON_PRETTY_PRINT));
            $this->translations = [];
        }

        // বেস ফাইলের কি (Key) গুলো টার্গেট ফাইলে না থাকলে ফাঁকা ভ্যালু দিয়ে যুক্ত করে নেওয়া
        foreach ($this->baseTranslations as $key => $value) {
            if (!array_key_exists($key, $this->translations)) {
                $this->translations[$key] = '';
            }
        }

        $this->calculateStats();
    }

    // পরিসংখ্যান হিসাব করার মেথড
    public function calculateStats()
    {
        $this->totalKeys = count($this->baseTranslations);
        $this->translatedKeys = collect($this->translations)->filter(fn($value) => !empty($value))->count();
        $this->missingKeys = $this->totalKeys - $this->translatedKeys;
    }

    // কোডবেস স্ক্যান করে অটোমেটিক __() কি-ওয়ার্ড খুঁজে বের করার মেথড
    public function scanCodebase()
    {
        if (!File::exists(resource_path('views'))) {
            Flux::toast('Views ফোল্ডারটি খুঁজে পাওয়া যায়নি!', variant: 'danger');
            return;
        }

        $finder = new Finder();
        // resources/views ফোল্ডারের ভেতরের সব .blade.php ফাইল খোঁজা হচ্ছে
        $finder->files()->in(resource_path('views'))->name('*.blade.php');

        $foundKeys = [];

        foreach ($finder as $file) {
            $content = $file->getContents();

            // Regex দিয়ে __('...') এবং __("...") এর ভেতরের টেক্সট ম্যাচ করা হচ্ছে
            preg_match_all('/__\([\'"](.*?)[\'"]\)/', $content, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    // ডাইনামিক ভেরিয়েবল বা কোড থাকলে স্কিপ করবে
                    if (!str_contains($match, '$') && !empty($match)) {
                        $foundKeys[] = $match;
                    }
                }
            }
        }

        $foundKeys = array_unique($foundKeys);
        $newKeysCount = 0;

        // নতুন পাওয়া কি (Key) গুলো ফাইলে ইনসার্ট করা
        foreach ($foundKeys as $key) {
            if (!array_key_exists($key, $this->baseTranslations)) {
                $this->baseTranslations[$key] = $key;
                $this->translations[$key] = '';
                $newKeysCount++;
            }
        }

        if ($newKeysCount > 0) {
            $this->saveTranslations();
            $this->loadTranslations();
            Flux::toast("স্ক্যান সম্পন্ন হয়েছে! {$newKeysCount}টি নতুন শব্দ পাওয়া গেছে।");
        } else {
            Flux::toast('নতুন কোনো শব্দ পাওয়া যায়নি। আপনার ফাইলগুলো আপ-টু-ডেট আছে।');
        }
    }

    public function editKey($key)
    {
        $this->editingKey = $key;
        $this->editEnValue = $this->baseTranslations[$key] ?? '';
        $this->editTargetValue = $this->translations[$key] ?? '';

        Flux::modal('edit-translation-modal')->show();
    }

    public function saveSingleKey()
    {
        if ($this->editingKey) {
            $this->baseTranslations[$this->editingKey] = $this->editEnValue;
            $this->translations[$this->editingKey] = $this->editTargetValue;

            $this->saveTranslations();

            Flux::modal('edit-translation-modal')->close();
            Flux::toast('সফলভাবে আপডেট করা হয়েছে!');
            $this->editingKey = null;
            $this->loadTranslations();
        }
    }

    public function addTranslation()
    {
        $this->validate([
            'newKey' => 'required|string',
            'newValue' => 'required|string',
        ]);

        $this->baseTranslations[$this->newKey] = $this->newKey;
        $this->translations[$this->newKey] = $this->newValue;

        $this->saveTranslations();

        $this->reset(['newKey', 'newValue']);
        Flux::modal('add-translation-modal')->close();
        Flux::toast('নতুন শব্দ যুক্ত করা হয়েছে!');
        $this->loadTranslations();
    }

    public function deleteTranslation($key)
    {
        if (isset($this->translations[$key])) {
            unset($this->translations[$key]);
            unset($this->baseTranslations[$key]);
            $this->saveTranslations();
            Flux::toast('ট্রান্সলেশন মুছে ফেলা হয়েছে!');
            $this->loadTranslations();
        }
    }

    private function saveTranslations()
    {
        File::put(lang_path('en.json'), json_encode($this->baseTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put(lang_path($this->locale . '.json'), json_encode($this->translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function render()
    {
        $filteredTranslations = collect($this->translations)->filter(function ($value, $key) {
            if (empty($this->search)) return true;
            return stripos($key, $this->search) !== false || stripos($value, $this->search) !== false;
        })->toArray();

        return view('livewire.admin.language-manager', [
            'filteredTranslations' => $filteredTranslations
        ])->layout('layouts.app', ['title' => 'Language Manager']);
    }
}
