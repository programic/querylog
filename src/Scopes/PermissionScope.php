<?php

namespace Programic\Permission\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Programic\Permission\PermissionQueryBuilder;

class PermissionScope implements Scope
{

    protected $permission;

    public function __construct($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        PermissionQueryBuilder::setGlobalScope($builder, $this->permissions, $model);
    }
}
