<?php

namespace App\Filament\Pages;

use App\Helpers\SettingHelper;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ContactSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $title = 'İletişim Ayarları';

    protected static ?string $slug = 'contact-settings';

    protected string $view = 'filament.pages.contact-settings';

    public ?array $data = [];

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
        $this->form->fill([
            'contact_address' => SettingHelper::get('contact_address'),
            'contact_email' => SettingHelper::get('contact_email'),
            'contact_phone' => SettingHelper::get('contact_phone'),
            'contact_form_to' => SettingHelper::get('contact_form_to', 'info@urundogrula.com'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('contact_address')
                    ->label('Adres')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('contact_email')
                    ->label('E-posta (Görünen)')
                    ->email()
                    ->required(),
                TextInput::make('contact_phone')
                    ->label('Telefon')
                    ->tel()
                    ->required(),
                TextInput::make('contact_form_to')
                    ->label('Form E-posta Alıcısı')
                    ->email()
                    ->helperText('İletişim formundan gelen mesajlar bu adrese gönderilir.')
                    ->required(),
            ])
            ->columns(2);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
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
