<?php

namespace App\Filament\Pages;

use App\Helpers\SettingHelper;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class ContactSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $title = 'İletişim Ayarları';

    protected static ?string $slug = 'contact-settings';

    protected string $view = 'filament.pages.contact-settings';

    public string $contact_address = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $contact_form_to = '';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->contact_address = (string) SettingHelper::get('contact_address', '');
        $this->contact_email = (string) SettingHelper::get('contact_email', '');
        $this->contact_phone = (string) SettingHelper::get('contact_phone', '');
        $this->contact_form_to = (string) SettingHelper::get('contact_form_to', 'info@urundogrula.com');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'contact_address' => 'required|string|max:500',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            'contact_form_to' => 'required|email|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => ['tr' => $value, 'en' => $value]]
            );
        }

        SettingHelper::forgetCache();

        Notification::make()
            ->title('İletişim ayarları kaydedildi.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Kaydet')
                ->action('save'),
        ];
    }
}
