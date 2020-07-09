<?php

namespace Programic\Permission\Traits;


use Illuminate\Database\Eloquent\Builder;
use Programic\Permission\Events\RoleAssigned;
use Programic\Permission\Events\RoleUnassigned;
use Programic\Permission\PermissionRegistrar;

trait HasRole
{
    public function roles()
    {
        $roleClass = app(PermissionRegistrar::class)->getRoleClass();
        return $this->belongsToMany($roleClass, 'role_user')
            ->withPivot(['confirmed', 'active', 'target_type', 'target_id']);
    }

    public function assignRole($roleName, $target = null)
    {
        $attach = [
            'user_id' => $this->id,
            'role_name' => $roleName,
        ];

        if ($target !== null) {
            $attach['target_type'] = get_class($target);
            $attach['target_id'] = $target->id;
        }

        $roleUserClass = app(PermissionRegistrar::class)->getRoleUserClass();
        $roleUser = $roleUserClass->firstOrCreate($attach, $attach);

        event(new RoleAssigned($roleUser));

        return $roleUser;
    }

    public function unassignRole($roleName, $target)
    {
        return $this->deleteRole($roleName, $target);
    }

    public function deleteRole($roleName, $target)
    {
        $roleUserClass = app(PermissionRegistrar::class)->getRoleUserClass();
        $roleUser = $roleUserClass->where('user_id', $this->id)
            ->where('target_type', get_class($target))
            ->where('target_id', $target->id)
            ->where('role_name', $roleName);

        event(new RoleUnassigned($roleUser->first()));

        return $roleUser->delete();
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeRole($query, $roleName)
    {
        return $query->whereHas('roles', function (Builder $query) use ($roleName) {
            $query->where('role_user.role_name', $roleName);
        });
    }

    /**
     * Scope a query to only include active users.
     *
     * @param Builder $query
     * @param $roles
     * @return Builder
     */
    public function scopeRoles(Builder $query, array $roles)
    {
        if (count($roles) > 0) {
            return $query->whereHas('roles', function (Builder $query) use ($roles) {
                $query->whereIn('role_user.role_name', $roles);
            });
        }

        return $query;
    }

    /**
     * Scope a query to only include active users.
     *
     * @param Builder $query
     * @param $targetType
     * @param $targetId
     * @return Builder
     */
    public function scopeTarget($query, $targetType, $targetId)
    {
        return $query->whereHas('roles', function (Builder $query) use ($targetType, $targetId) {
            $query->where('role_user.target_type', $targetType)
                ->where('role_user.target_id', $targetId);
        });
    }

    /**
     * @param Builder $query
     * @param $targetPath
     * @return Builder
     */
    public function scopeTargetPath($query, $targetPath)
    {
        $query->whereHas('permissions', function (Builder $query) use ($targetPath) {
            $query->whereNull('target_path')
                ->orWhere('target_path', 'LIKE', $targetPath . '%')
                ->whereTargetPath($targetPath);

            return $query;
        });
    }
}
