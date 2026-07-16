<?php

namespace App\Livewire;

use Livewire\Component;

class AdminLanguageSwitcher extends Component
{
    public function switch(string $locale)
    {
        if (!auth()->check()) {
            return;
        }

        if (!array_key_exists($locale, config('languages.supported', []))) {
            return;
        }

        session()->put('locale', $locale);
        session()->save();
        app()->setLocale($locale);

        $referer = request()->header('Referer', '/admin');
        $host = request()->getSchemeAndHttpHost();
        $redirect = str_starts_with($referer, $host) ? $referer : '/admin';

        return redirect($redirect);
    }

    public function render()
    {
        return view('livewire.admin-language-switcher');
    }
}