<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::create([
            "name" => "owner"
        ]);
        $fundraiserRole = Role::create([
            "name" => "fundraiser"
        ]);

        $userOwner = User::create([
            'name' => 'M. Rikza As-subhy',
            'avatar' => 'images/default-avatar.png',
            'email' => 'rikza@bagipanen.com',
            'password' => bcrypt("135246")
        ]);

        $userOwner->assignRole($ownerRole);
    }
}
