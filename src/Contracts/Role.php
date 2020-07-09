<?php

namespace Programic\Permission\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Role
{
    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions();

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users();

    /**
     * Determine if the user may perform the given permission.
     *
     * @param array|string $permissions
     *
     * @return self
     */
    public function givePermission($permissions);

    /**
     * @param array|string $permissions
     * @return self
     */
    public function revokePermission($permissions);
}
