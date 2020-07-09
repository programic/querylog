<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Programic\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    private $permissionRegistar;

    protected $horizontalPermissions = [
        'create-user' => [
            'send-user-invitations',
        ],
        'update-user' => [
            'send-password-resets',
            'change-user-states',
        ],
        'manage-users' => [
            'create-user', 'delete-user', 'list-users', 'update-user',
        ],
    ];

    protected $downPermissions = [
    ];

    protected $upPermissions = [

    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->writeInheritancePermissions($this->horizontalPermissions, 'hor');
        $this->writeInheritancePermissions($this->downPermissions, 'down');
        $this->writeInheritancePermissions($this->upPermissions, 'up');
    }

    private function writeInheritancePermissions($group, $direction)
    {
        $permissionRegistar = app(PermissionRegistrar::class);
        $permissionClass = $permissionRegistar->getPermissionClass();
        $permissionInheritanceClass = $permissionRegistar->getPermissionInheritanceClass();

        foreach ($group as $permission => $permissions) {
            $permissionClass->firstOrCreate([
                'name' => $permission,
            ]);

            foreach ($permissions as $permission_name) {
                $permissionClass->firstOrCreate([
                    'name' => $permission_name,
                ]);

                $permissionInheritanceClass->firstOrCreate([
                    'source_permission_name' => $permission,
                    'target_permission_name' => $permission_name,
                ], [
                    'source_permission_name' => $permission,
                    'target_permission_name' => $permission_name,
                    'direction' => $direction,
                ]);
            }
        }
    }
}
