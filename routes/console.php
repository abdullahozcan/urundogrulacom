<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:super-admin
    {identifier : User email or id}
    {--create : Create user if not exists}
    {--name= : Name for created user}
    {--password= : Password for created user}', function () {
    $identifier = (string) $this->argument('identifier');

    $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
        ? User::where('email', $identifier)->first()
        : User::find($identifier);

    if (!$user && !$this->option('create')) {
        $this->error('Kullanici bulunamadi. Olusturmak icin --create kullan.');
        return self::FAILURE;
    }

    if (!$user) {
        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $this->error('Yeni kullanici olusturmak icin identifier bir email olmali.');
            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: 'password');
        $name = (string) ($this->option('name') ?: 'Super Admin');

        $user = User::create([
            'name' => $name,
            'email' => $identifier,
            'password' => Hash::make($password),
            'company_id' => null,
        ]);

        $this->warn("Kullanici olusturuldu. Gecici sifre: {$password}");
    }

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    $this->info("{$user->email} artik super_admin.");

    return self::SUCCESS;
})->purpose('Make an existing user super admin (or create first one)');

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
