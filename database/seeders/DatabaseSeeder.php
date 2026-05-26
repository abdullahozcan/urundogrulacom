<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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

        $companyAdmin = User::firstOrCreate(
            ['email' => 'companyadmin@example.com'],
            [
                'name' => 'Company Admin',
                'password' => 'password',
                'company_id' => $company->id,
            ]
        );
        $companyAdmin->assignRole($companyAdminRole);

        Category::firstOrCreate(
            ['company_id' => $company->id, 'slug' => 'genel-kategori'],
            [
                'name' => 'Genel Kategori',
                'is_active' => true,
            ]
        );
    }
}
