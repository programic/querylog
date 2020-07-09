<?php

namespace Programic\Permission;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Programic\Permission\Contracts\Role as RoleContract;
use Programic\Permission\Contracts\Permission as PermissionContract;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot(PermissionRegistrar $permissionLoader, Filesystem $filesystem)
    {
        // publish config
        $this->publishes([
            __DIR__.'/../config/permission.php' => config_path('permission.php'),
        ], 'config');

        // publish migration files
        $this->publishes($this->getMigrationFileNames($filesystem), 'migrations');


        $this->publishes([
            __DIR__.'/../database/seeds/PermissionsSeeder.php' => database_path().'/seeds/PermissionsSeeder.php',
            __DIR__.'/../database/seeds/RolesSeeder.php' => database_path().'/seeds/RolesSeeder.php',
            __DIR__.'/../database/seeds/RoleUserSeeder.php' => database_path().'/seeds/RoleUsersSeeder.php',
        ], 'seeds');


        $this->commands([
            Commands\SyncUserPermissions::class,
        ]);

        $this->registerModelBindings();
        $this->registerMacroHelpers();

        $permissionLoader->registerPermissions();

        $this->app->singleton(PermissionRegistrar::class, function ($app) use ($permissionLoader) {
            return $permissionLoader;
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/permission.php',
            'permission'
        );

        $this->registerBladeExtensions();
    }

    protected function registerModelBindings()
    {
        $config = $this->app->config['permission.models'];

        if (! $config) {
            return;
        }

        $this->app->bind(PermissionContract::class, $config['permission']);
        $this->app->bind(RoleContract::class, $config['role']);
    }

    protected function registerMacroHelpers()
    {
        Route::macro('role', function ($roles = []) {
            if (! is_array($roles)) {
                $roles = [$roles];
            }

            $roles = implode('|', $roles);

            $this->middleware("role:$roles");

            return $this;
        });

        Route::macro('permission', function ($permissions = []) {
            if (! is_array($permissions)) {
                $permissions = [$permissions];
            }

            $permissions = implode('|', $permissions);

            $this->middleware("permission:$permissions");

            return $this;
        });
    }

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('role', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('elserole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php elseif(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasrole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endhasrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasanyrole', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAnyRole({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasallroles', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAllRoles({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('unlessrole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(!auth({$guard})->check() || ! auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endunlessrole', function () {
                return '<?php endif; ?>';
            });
        });
    }

    /**
     * Copies migration files to project with the timestamps
     *
     * @param Filesystem $filesystem
     * @return array
     */
    protected function getMigrationFileNames(Filesystem $filesystem): array
    {
        $timestamp = date('Y_m_d_His');
        $migrations = collect([
            '*1_create_roles_table.php',
            '*2_create_permissions_table.php',
            '*3_create_permission_role.php',
            '*4_create_permission_inheritances_table.php',
            '*5_create_role_user.php',
            '*6_create_permission_user.php',
        ]);

        $oldPath = __DIR__.'/../database/migrations/';
        $newPath = database_path().'/migrations/';
        return $migrations->mapWithKeys(function ($file) use ($timestamp, $oldPath, $newPath) {
            $key = $oldPath.$file;
            $migration = $newPath.str_replace('*', $timestamp, $file);
            return [$key => $migration];
        })->toArray();
    }
}
