<?php

namespace Programic\Permission\Traits;

use Illuminate\Support\Collection;
use Programic\Permission\PermissionQueryBuilder;
use Programic\Permission\PermissionRegistrar;

trait HasPermissions
{
    /**
     * @param $target
     * @return PermissionQueryBuilder
     */
    public function permissionsForTarget($target) : PermissionQueryBuilder
    {
        return new PermissionQueryBuilder($this->id, $target);
    }

    /**
     * @return PermissionQueryBuilder
     */
    public function globalPermissions() : PermissionQueryBuilder
    {
        return new PermissionQueryBuilder($this->id);
    }

    /**
     * @param array $permissions
     * @param null $target
     * @return Collection
     */
    public function getPermissions($permissions = [], $target = null)
    {
        return $this->getPermission($permissions, $target);
    }

    /**
     * @param $permission
     * @param null $target
     * @return Collection
     */
    public function getPermission($permission, $target = null)
    {
        return $this->permissionsForTarget($target)
            ->permission($permission)
            ->includeUp()
            ->includeDown()
            ->get();
    }

    /**
     * @param $permission
     * @param null $target
     * @return bool
     */
    public function hasPermission($permission, $target = null) : bool
    {
        return $this->getPermission($permission, $target)->isNotEmpty();
    }

    /**
     * @param array $permissions
     * @param null $target
     * @return bool
     */
    public function hasAnyPermission($permissions = [], $target = null) : bool
    {
        return $this->getPermissions($permissions, $target)->isNotEmpty();
    }

    /**
     * @param array $permissions
     * @param null $target
     * @return bool
     */
    public function hasAllPermissions($permissions = [], $target = null) : bool
    {
        return $this->getPermissions($permissions, $target)->count() === count($permissions);
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role) : bool
    {

        return app(PermissionRegistrar::class)->getRoleUserClass()->where('user_id', $this->id)
            ->where('role_name', $role)
            ->exists();
    }

    /**
     * @param mixed ...$roles
     * @return bool
     */
    public function hasAnyRole(...$roles) : bool
    {
        return app(PermissionRegistrar::class)->getRoleUserClass()->where('user_id', $this->id)
            ->whereIn('role_name', $roles)
            ->exists();
    }

    /**
     * @param mixed ...$roles
     * @return bool
     */
    public function hasAllRoles(...$roles) : bool
    {
        return app(PermissionRegistrar::class)->getRoleUserClass()->where('user_id', $this->id)
            ->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->where('role_name', $role);
                }
            })
            ->exists();
    }

    /**
     * @return mixed
     */
    public function permissions()
    {
        return $this->belongsToMany(app(PermissionRegistrar::class)->getPermissionClass(), 'permission_user');
    }

    /**
     * @param $permissions
     * @param $target
     * @return array
     */
    public function getTargetsFromPermissions(array $permissions, $target)
    {
        $targetClass = get_class($target);
        $abbreviation = array_search($targetClass, config('permission.abbreviation'));

        $targets = $this->globalPermissions()
            ->permissions($permissions)
            ->includeUp()
            ->includeDown()
            ->get()
            ->pluck('target_path');

        $targets = $this->findAbbreviationInTargetPath($abbreviation, $targets, $target);

        return $targets;
    }

    /**
     * @param $abbreviation
     * @param $targetPaths
     * @return array
     */
    private function findAbbreviationInTargetPath($abbreviation, $targetPaths)
    {
        $targets = [];
        $possibleAbbreviations = array_keys(config('permission.abbreviation'));
        foreach ($possibleAbbreviations as $possibleAbbreviation) {
            $targets[$possibleAbbreviation] = [];
            $targets['all_' . $possibleAbbreviation] = [];
            $targets['null_' . $possibleAbbreviation] = false;
        }

        $targetPaths->each(function ($targetPath) use ($abbreviation, &$targets) {
            if (is_null($targetPath)) {
                $targets['null_'.$abbreviation] = true;
            } elseif ($targetPath) {
                $targetChunks = explode('-', $targetPath);
                $last = array_pop($targetChunks);
                $abbreviation = $last[0];
                $abbreviationId = (int) filter_var($last, FILTER_SANITIZE_NUMBER_INT);

                $targets[$abbreviation][] = $abbreviationId;
                $targets['all_' . $abbreviation][] = $abbreviationId;

                foreach ($targetChunks as $chunk) {
                    $abbreviation = $chunk[0];
                    $abbreviationId = (int) filter_var($chunk, FILTER_SANITIZE_NUMBER_INT);

                    $targets['all_' . $abbreviation][] = $abbreviationId;
                }
            }
        });

        return $targets;
    }
}
