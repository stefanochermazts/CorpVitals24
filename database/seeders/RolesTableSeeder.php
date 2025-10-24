<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'manager', 'viewer'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}


