<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Artisan::command('user:grant-role', function () {
    $email = (string) $this->ask('Yetki verilecek kullanicinin e-posta adresi nedir?');

    if ($email === '') {
        $this->error('E-posta bos birakilamaz.');
        return self::FAILURE;
    }

    $user = User::where('email', $email)->first();

    if (!$user) {
        $this->error('Bu e-posta ile kullanici bulunamadi.');
        return self::FAILURE;
    }

    $roles = Role::query()
        ->where('guard_name', 'web')
        ->orderBy('name')
        ->pluck('name')
        ->all();

    if ($roles === []) {
        $this->error('Atanabilir rol bulunamadi.');
        return self::FAILURE;
    }

    $selectedRole = $this->choice(
        "Kullanici bulundu: {$user->name} <{$user->email}>. Hangi rolu atamak istersin?",
        $roles
    );

    if (!$this->confirm("`{$selectedRole}` rolu atansin mi?", true)) {
        $this->warn('Islem iptal edildi.');
        return self::SUCCESS;
    }

    $user->assignRole($selectedRole);

    $this->info("Islem tamamlandi. {$user->email} kullanicisina `{$selectedRole}` rolu atandi.");

    return self::SUCCESS;
})->purpose('Ask email, find user, and assign selected role interactively');
