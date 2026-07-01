<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $companyAdminRole = Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
        $company = Company::firstOrCreate(
            ['name' => 'Demo Company'],
            ['is_active' => true]
        );

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'company_id' => null,
            ]
        );
        $superAdmin->assignRole($superAdminRole);
        $superAdminRole->syncPermissions(Permission::query()->pluck('name')->all());

        $company->update(['max_sub_companies' => 5]);

        $subCompanyA = Company::firstOrCreate(
            ['name' => 'Demo Alt Firma A'],
            ['is_active' => true, 'parent_id' => $company->id]
        );

        $subCompanyB = Company::firstOrCreate(
            ['name' => 'Demo Alt Firma B'],
            ['is_active' => true, 'parent_id' => $company->id]
        );

        $companyAdmin = User::firstOrCreate(
            ['email' => 'companyadmin@example.com'],
            [
                'name' => 'Company Admin',
                'password' => 'password',
                'company_id' => $company->id,
            ]
        );
        $companyAdmin->assignRole($companyAdminRole);

        User::firstOrCreate(
            ['email' => 'subcompany_a@example.com'],
            [
                'name' => 'Alt Firma A Admin',
                'password' => 'password',
                'company_id' => $subCompanyA->id,
            ]
        )->assignRole($companyAdminRole);

        User::firstOrCreate(
            ['email' => 'subcompany_b@example.com'],
            [
                'name' => 'Alt Firma B Admin',
                'password' => 'password',
                'company_id' => $subCompanyB->id,
            ]
        )->assignRole($companyAdminRole);

        Category::firstOrCreate(
            ['company_id' => $company->id, 'slug' => 'genel-kategori'],
            [
                'name' => 'Genel Kategori',
                'is_active' => true,
            ]
        );

        // Varsayılan iletişim ayarları
        $defaultSettings = [
            'contact_address' => 'Teknoloji Geliştirme Bölgesi, Ar-Ge Binası No: 1, İstanbul',
            'contact_email'   => 'info@urundogrula.com',
            'contact_phone'   => '+90 (850) 123 45 67',
            'contact_form_to' => 'info@urundogrula.com',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => ['tr' => $value, 'en' => $value]]
            );
        }
    }
}
