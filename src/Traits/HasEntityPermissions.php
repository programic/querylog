<?php

namespace Programic\Permission\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Programic\Permission\PermissionQueryBuilder;
use Programic\Permission\PermissionRegistrar;

trait HasEntityPermissions
{
    public function roleUsers()
    {
        return $this->morphMany(app(PermissionRegistrar::class)->getRoleUserClass(), 'target');
    }

    public function users()
    {
        return $this->getUsersWithRole();
    }

    public function getUsersAttribute()
    {
        return $this->users()->get();
    }

    public function userHasRole($role, $user = null)
    {
        if ($user === null) {
            $user = auth()->user();
        }

        $target = get_class($this);
        $targetId = $this->id;


        return app(PermissionRegistrar::class)->getUserClass()->target($target, $targetId)
            ->role($role)
            ->where('id', $user->id)
            ->exists();
    }

    public function userHasAnyRole($roles, $user = null)
    {
        if ($user === null) {
            $user = auth()->user();
        }

        $target = get_class($this);
        $targetId = $this->id;

        return app(PermissionRegistrar::class)->getUserClass()->target($target, $targetId)
            ->roles($roles)
            ->where('id', $user->id)
            ->exists();
    }

    public function getRolesByUser($user = null)
    {
        if ($user === null) {
            $user = auth()->user();
        }

        $target = get_class($this);
        $targetId = $this->id;

        return app(PermissionRegistrar::class)->getUserClass()->target($target, $targetId)
            ->where('id', $user->id)
            ->get();
    }

    public function getPermissions($user = null)
    {
        if ($user === null) {
            $user = auth()->user();
        }
        $targetPath = $this->targetPath();

        return app(PermissionRegistrar::class)->getPermissionClass()
            ->whereHas('users', function (Builder $query) use ($user, $targetPath) {
                $query->where('user_id', $user->id)
                    ->targetPath($targetPath);
            })
            ->get();
    }

    /**
     * // Retrieve Users grouped by roles
     * @param mixed ...$roles
     * @return Collection
     */
    public function getUsersByRole(...$roles)
    {
        $roleQuery = app(PermissionRegistrar::class)->getRoleClass()->with('users');
        if (count($roles) > 0) {
            $roleQuery->whereIn('name', $roles);
        }

        return $roleQuery->get();
    }

    /**
     * Collection met users within entity
     * @param string $role
     * @return User|Builder
     */
    public function getUsersWithRole($role = null)
    {
        $targetPath = $this->targetPath();
        $query = app(PermissionRegistrar::class)->getUserClass()->with('roles');
        if ($role) {
            $query->role($role);
        }

        return $query->targetPath($targetPath);
    }

    /**
     * @param mixed ...$permissions
     * @return Collection
     */
    public function getUsersByPermission(...$permissions)
    {
        $targetPath = $this->targetPath();

        return app(PermissionRegistrar::class)->getUserClass()->with('permissions')
            ->whereIn('permission_name', $permissions)
            ->targetPath($targetPath)
            ->get();
    }

    public function getPermissionsAttribute()
    {
        return $this->getPermissions();
    }

    public function scopePermission(Builder $query, $permission, $userId = null)
    {
        return PermissionQueryBuilder::setGlobalScope($query, $permission, $this, $userId);
    }
}
