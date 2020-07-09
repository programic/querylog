<?php

namespace Programic\Permission\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Programic\Permission\Contracts\Permission as PermissionContract;
use Programic\Permission\PermissionRegistrar;

class Permission extends Model implements PermissionContract
{
    protected $guarded = ['id'];
    protected $hidden = ['guard'];
    protected $primaryKey = 'name';

    public $timestamps = false;
    public $incrementing = false;

    public function roles()
    {
        $roleClass = app(PermissionRegistrar::class)->getRoleClass();
        $permissionRoleClass = app(PermissionRegistrar::class)->getPermissionRoleClass();

        return $this->belongsToMany($roleClass)
            ->using($permissionRoleClass);
    }

    public function users()
    {
        $userClass = app(PermissionRegistrar::class)->getUserClass();
        $permissionUserClass = app(PermissionRegistrar::class)->getPermissionUserClass();

        return $this->belongsToMany($userClass)
            ->using($permissionUserClass);
    }

    /**
     * @param Builder $query
     * @param $targetPath
     * @return Builder
     */
    public function scopeTargetPath($query, $targetPath)
    {
        return $query->where(function (Builder $query) use ($targetPath) {
            $query->whereNull('target_path')
                ->orWhere('target_path', 'LIKE', $targetPath . '%')
                ->whereTargetPath($targetPath);
        });
    }
}
