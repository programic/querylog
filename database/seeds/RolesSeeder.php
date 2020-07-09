<?php

use Illuminate\Database\Seeder;
use Programic\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleClass = app(PermissionRegistrar::class)->getRoleClass();

        $roleClass->create([
            'name' => 'admin',
            'title' => 'Administrator'
        ])->givePermission([
            'manage-users',
        ]);

    }
}
