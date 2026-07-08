<?php

namespace App\Livewire;

use Livewire\Component;

class AdminLanguageSwitcher extends Component
{
    public function switch(string $locale)
    {
        if (!array_key_exists($locale, config('languages.supported', []))) {
            return;
        }

        session()->put('locale', $locale);
        session()->save();
        app()->setLocale($locale);

        return redirect(request()->header('Referer', '/admin'));
    }

    public function render()
    {
        return view('livewire.admin-language-switcher');
    }
}