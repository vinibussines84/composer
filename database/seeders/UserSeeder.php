<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Xota',
            'email' => 'adminxota@xota.com',
            'password' => Hash::make('senha123'),
            'taxa_cash_in' => 2.5,
            'taxa_cash_out' => 1.75,
            'authkey' => 'abc123xyz',
            'gtkey' => 'gt-001',
            'senha' => 'api@xota',
            'cashin_ativo' => true,
            'cashout_ativo' => true,
            'dashboard_access' => 3,
        ]);

        User::factory(5)->create([
            'dashboard_access' => 3,
            'taxa_cash_in' => 1.5,
            'taxa_cash_out' => 2.0,
            'cashin_ativo' => true,
            'cashout_ativo' => false,
        ]);
    }
}
