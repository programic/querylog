<?php

namespace Programic\Permission;

use Illuminate\Support\Collection;
use Programic\Permission\Contracts\Role;
use Illuminate\Contracts\Auth\Access\Gate;
use Programic\Permission\Contracts\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;

class PermissionRegistrar
{
    /** @var string */
    protected $userClass;

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $roleClass;

    /** @var string */
    protected $roleUserClass;

    /** @var string */
    protected $permissionUserClass;

    /** @var string */
    protected $permissionRoleClass;

    /** @var string */
    protected $permissionInheritanceClass;

    /**
     * PermissionRegistrar constructor.
     */
    public function __construct()
    {
        $this->userClass = config('permission.models.user');
        if (empty($this->userClass)) {
            $this->userClass = config('auth.providers.users.model');
        }

        $this->permissionClass = config('permission.models.permission');
        $this->roleClass = config('permission.models.role');
        $this->roleUserClass = config('permission.models.role_user');
        $this->permissionUserClass = config('permission.models.permission_user');
        $this->permissionRoleClass = config('permission.models.permission_role');
        $this->permissionInheritanceClass = config('permission.models.permission_inheritance');
    }

    /**
     * Register the permission check method on the gate.
     * We resolve the Gate fresh here, for benefit of long-running instances.
     *
     * @return bool
     */
    public function registerPermissions(): bool
    {
        app(Gate::class)->before(function (Authorizable $user, string $ability) {
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability) ?: null;
            }
        });

        return true;
    }

    /**
     * Get an instance of the permission class.
     */
    public function getPermissionClass()
    {
        return app($this->permissionClass);
    }

    /**
     * Get an instance of the role class.
     */
    public function getRoleClass()
    {
        return app($this->roleClass);
    }


    public function getRoleUserClass()
    {
        return app($this->roleUserClass);
    }

    public function getPermissionUserClass()
    {
        return app($this->permissionUserClass);
    }

    public function getPermissionRoleClass()
    {
        return app($this->permissionRoleClass);
    }

    public function getUserClass()
    {
        return app($this->userClass);
    }

    public function getPermissionInheritanceClass()
    {
        return app($this->permissionInheritanceClass);
    }
}
