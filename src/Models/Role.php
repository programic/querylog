<?php

namespace Programic\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Programic\Permission\Contracts\Role as RoleContract;


class Role extends Model implements RoleContract
{
    protected $guarded = [];
    protected $hidden = ['guard'];
    protected $primaryKey = 'name';

    public $timestamps = false;
    public $incrementing = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class)
            ->using(PermissionRole::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot(['confirmed', 'active', 'target_type', 'target_id']);
    }

    public function targetUsers($target)
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->wherePivot('target_type', get_class($target))
            ->wherePivot('target_id', $target->id)
            ->withPivot(['confirmed', 'active', 'target_type', 'target_id']);
    }

    /**
     * @param array|string $permissions
     * @return $this
     */
    public function givePermission($permissions) : self
    {
        if (is_array($permissions) === false) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            $this->permissions()->attach($permission);
        }

        return $this;
    }

    /**
     * @param array|string $permissions
     * @return $this
     */
    public function revokePermission($permissions) : self
    {
        if (is_array($permissions) === false) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            $this->permissions()->detach($permission);
        }

        return $this;
    }
}
