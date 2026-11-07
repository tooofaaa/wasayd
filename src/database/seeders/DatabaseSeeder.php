<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $company = Company::create([
            'name' => 'WASAYD Demo Hospital',
            'name_ar' => 'مستشفى وسيد التجريبي',
            'code' => 'WDH',
            'country' => 'SA',
            'currency' => 'SAR',
            'timezone' => 'Asia/Riyadh',
            'locale' => 'ar',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Main Branch',
            'name_ar' => 'الفرع الرئيسي',
            'code' => 'MB',
            'city' => 'Riyadh',
            'is_active' => true,
        ]);

        $admin = User::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Super Admin',
            'name_ar' => 'المدير العام',
            'email' => 'admin@wasayd.com',
            'password' => Hash::make('password'),
            'locale' => 'ar',
            'theme' => 'light',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('super-admin');
    }
}
