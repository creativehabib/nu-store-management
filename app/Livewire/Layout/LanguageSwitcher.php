<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    public function changeLanguage($lang)
    {
        if (in_array($lang, ['en', 'bn'])) {
            Session::put('locale', $lang);

            if (Auth::check()) {
                Auth::user()->update(['locale' => $lang]);
            }
            App::setLocale($lang);
        }
        $this->redirect(request()->header('Referer') ?? '/dashboard');
    }

    public function render()
    {
        $currentLocale = Auth::check() ? Auth::user()->locale : Session::get('locale', config('app.locale'));
        return view('livewire.layout.language-switcher', ['currentLocale' => $currentLocale]);
    }
}
