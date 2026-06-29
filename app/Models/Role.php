<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'display_name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withPivot('assigned_by', 'assigned_at');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Sync permissions for this role by permission names.
     */
    public function syncPermissionsByName(array $permissionNames): void
    {
        $ids = Permission::whereIn('name', $permissionNames)->pluck('id');
        $this->permissions()->sync($ids);
    }
}
