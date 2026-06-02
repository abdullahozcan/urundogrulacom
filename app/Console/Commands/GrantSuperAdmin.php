<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GrantSuperAdmin extends Command
{
    protected $signature = 'user:super-admin
        {email : Kullanici e-posta adresi}
        {--create : Kullanici yoksa olustur}
        {--name= : Olusturulacak kullanicinin adi}
        {--password= : Olusturulacak kullanicinin sifresi}';

    protected $description = 'E-posta ile kullaniciyi bulup super admin yetkisi verir';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user && !$this->option('create')) {
            $this->error("Kullanici bulunamadi: {$email}");
            $this->warn('Kullaniciyi olusturmak icin --create kullanabilirsiniz.');

            return self::FAILURE;
        }

        if (!$user) {
            $password = (string) ($this->option('password') ?: 'password');
            $name = (string) ($this->option('name') ?: 'Super Admin');

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
                'company_id' => null,
            ]);

            $this->warn("Kullanici olusturuldu. Gecici sifre: {$password}");
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(Permission::query()->pluck('name')->all());

        $user->company_id = null;
        $user->save();

        $user->assignRole($role);

        $this->info("{$user->email} kullanicisina super_admin yetkisi verildi.");

        return self::SUCCESS;
    }
}
