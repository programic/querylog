<?php

namespace Programic\Permission\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Programic\Permission\PermissionRegistrar;

class RoleUser extends Model
{
    protected $guarded = [];
    protected $table = 'role_user';


    public function target()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(app(PermissionRegistrar::class)->getUserClass());
    }

    public function role()
    {
        return $this->belongsTo(app(PermissionRegistrar::class)->getRoleClass());
    }

    /**
     * @param Builder $query
     * @param $target
     */
    public function scopeTarget(Builder $query, $target)
    {
        $query->where('target_type', get_class($target))
            ->where('target_id', $target->id);
    }

    /**
     * @param Builder $query
     * @param $permission
     */
    public function scopeRole(Builder $query, $permission)
    {
        $query->where('role_name', $permission);
    }

    /**
     * @param Builder $query
     * @param array $permissions
     */
    public function scopeRoles(Builder $query, array $permissions)
    {
        $query->whereIn('role_name', $permissions);
    }

    /**
     * @param Builder $query
     * @param int $confirmed
     */
    public function scopeConfirmed(Builder $query, $confirmed = 1)
    {
        $query->where('confirmed', $confirmed);
    }

    /**
     * @param Builder $query
     * @param int $active
     */
    public function scopeActive(Builder $query, $active = 1)
    {
        $query->where('active', $active);
    }
}
