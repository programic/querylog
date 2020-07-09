<?php

namespace Programic\Permission\Commands;

use hisorange\BrowserDetect\Exceptions\Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Programic\Permission\PermissionRegistrar;
use Programic\Permission\Scopes\PermissionScope;

class SyncUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync {--user=} {--target-type=} {--target-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user permissions';

    protected $inheritances = [];
    protected $permission_ids = [];

    protected $permissionClass;
    protected $permissionUserClass;

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $this->permissionUserClass = $permissionRegistrar->getPermissionUserClass();
        $this->permissionClass = $permissionRegistrar->getPermissionClass();

        $this->inheritances = $permissionRegistrar->getPermissionInheritanceClass()->all();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $userRoles = $this->optionsQuery($permissionRegistrar->getRoleUserClass()->query())->get();

        foreach ($userRoles as $role_user) {

            $permissions = $permissionRegistrar->getPermissionRoleClass()->where('role_name', $role_user->role_name)->get();
            foreach ($permissions as $permission) {
                $this->recursive($permission, $role_user);
            }
        }

        $this->optionsQuery($this->permissionUserClass->query())
            ->whereNotIn('id', $this->permission_ids)
            ->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->output->success('Permissions synced');
    }

    /**
     * @param $permission
     * @param $role_user
     * @param null $causer_id
     * @param int $depth
     * @throws Exception
     */
    private function recursive($permission, $role_user, $causer_id = null, $depth = 0)
    {
        $path = '';
        if ($role_user->target_type && $role_user->target_id) {
            $targetModel = ($role_user->target_type)::withoutGlobalScope(PermissionScope::class)
                ->find($role_user->target_id);
            if ($targetModel) {
                $path = $targetModel->targetPath();
            } else {
                return;
            }
        }

        $causer = $this->permissionUserClass->firstOrCreate([
            'role_user_id' => $role_user->id,
            'user_id' => $role_user->user_id,
            'target_path' => ($path) ? $path : null,
            'target_type' => $role_user->target_type,
            'target_id' => $role_user->target_id,
            'causer_id' => $causer_id,
            'permission_name' => $permission->permission_name ?? $permission->name,
            'depth' => $depth
        ]);
        $this->permission_ids[] = $causer->id;

        $permissionName = $permission->permission_name ?? $permission->name;
        $permissions = $this->inheritances->where('source_permission_name', $permissionName);
        if (count($permissions)) {
            foreach ($permissions as $inheritancePermission) {
                $target = $this->permissionClass->find($inheritancePermission->target_permission_name);
                $this->recursive($target, $role_user, $causer->id, $depth++);
            }
        }
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    private function optionsQuery(Builder $query)
    {
        $userId = $this->option('user');
        $targetType = $this->option('target-type');
        $targetId = $this->option('target-id');

        return $query->when($userId, function (Builder $query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->when($targetType, function (Builder $query) use ($targetType) {
            $query->where('target_type', $targetType);
        })
        ->when($targetId, function (Builder $query) use ($targetId) {
            $query->where('target_id', $targetId);
        });
    }
}
