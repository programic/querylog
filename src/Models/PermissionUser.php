<?php

namespace Programic\Permission\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PermissionUser extends Model
{
    protected $table = 'permission_user';
    protected $guarded = [];
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(config('permission.models')['user']);
    }

    public function roleUser()
    {
        return $this->belongsTo(config('permission.models')['role_user']);
    }

    /**
     * @param Builder $query
     * @param $targetPath
     * @return Builder
     */
    public function scopeTargetPath($query, $targetPath)
    {
        $query->whereNull('target_path')
            ->orWhere('target_path', 'LIKE', $targetPath . '%')
            ->whereTargetPath($targetPath);

        return $query;
    }
}
