<?php

use Illuminate\Database\Seeder;
use Programic\Permission\PermissionRegistrar;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userClass = app(PermissionRegistrar::class)->getRoleClass();

        $userClass->all()->random(1)
            ->assignRole('admin');
    }
}
