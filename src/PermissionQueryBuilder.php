<?php

namespace Programic\Permission;

use App\Models\PermissionUser;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class PermissionQueryBuilder
{
    private $query = null;

    protected $target;
    protected $includeUp = false;
    protected $includeDown = false;
    protected $userId = null;
    protected $permissions = [];

    public function __construct($userId, $target = null)
    {
        $this->userId = $userId;
        if ($target) {
            $this->target = $target;
        }
    }

    public function __call($name, $arguments)
    {
        return $this->buildQuery()->get()->{$name}($arguments);
    }

    public function get() : Collection
    {
        return $this->buildQuery()->query->get();
    }

    public function permission($permission)
    {
        if (is_array($permission)) {
            return $this->permissions($permission);
        }

        $this->permissions[] = $permission;

        return $this;
    }

    public function permissions(array $permissions)
    {
        $this->permissions = array_merge($this->permissions, $permissions);

        return $this;
    }

    private function buildQuery() : self
    {
        if ($this->query !== null) {
            return $this->query;
        }

        $query = $roleClass = app(PermissionRegistrar::class)->getPermissionUserClass()->query();

        if ($this->target) {
            $targetPath = $this->target->targetPath();

            $query->where(function ($query) use ($targetPath) {
                $query->whereNull('target_path')
                    ->orWhere('target_path', 'LIKE', $targetPath . '%');

                if ($this->includeDown) {
                    $query->whereTargetPath($targetPath);
                }
            });
        }

        if ($this->permissions) {
            $query->whereIn('permission_name', $this->permissions);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        $this->query = $query;

        return $this;
    }

    public function getQuery() : Builder
    {
        return $this->buildQuery()->query;
    }

    public function includeUp(bool $bool = true) : self
    {
        $this->includeUp = $bool;

        return $this;
    }

    public function includeDown(bool $bool = true) : self
    {
        $this->includeDown = $bool;

        return $this;
    }

    public function user(int $userId) : self
    {
        $this->userId = $userId;

        return $this;
    }

    public static function setGlobalScope($builder, $permissions, $model, $userId = null)
    {
        if (auth()->check() || $userId) {
            if (is_array($permissions) === false) {
                $permissions = [$permissions];
            }

            if ($userId) {
                $user = app(PermissionRegistrar::class)->getUserClass()->find($userId);
            } else {
                $user = auth()->user();
            }

            $targets = $user->getTargetsFromPermissions($permissions, $model);

            $builder->where(function ($query) use ($targets, $permissions) {
                $magazinePermission = in_array('view-magazine', $permissions) && $targets['null_m'] === false;
                $query->when($magazinePermission, function ($permissionQuery) use ($targets) {
                    $permissionQuery->whereIn('magazines.id', $targets['all_m']);
                });

                $editionPermission = in_array('view-edition', $permissions) && $targets['null_e'] === false;
                $query->when($editionPermission, function ($permissionQuery) use ($targets) {
                    $permissionQuery->whereIn('magazine_id', $targets['m'])
                        ->orWhereIn('editions.id', $targets['all_e']);
                });

                $articlePermission = in_array('view-article', $permissions) && $targets['null_a'] === false;
                $query->when($articlePermission, function ($permissionQuery) use ($targets) {
                    $permissionQuery->whereIn('magazine_id', $targets['m'])
                        ->orWhereIn('edition_id', $targets['e'])
                        ->orWhereIn('articles.id', $targets['a']);
                });
            });
        }

        return $builder;
    }
}
