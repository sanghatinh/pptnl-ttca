<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionRole;
use App\Models\RoleComponent;

class RolePermissionController extends Controller
{
    public function store(Request $request)
    {
        $role_id = $request->input('role_id');
        $permissions = $request->input('permissions');
        $components = $request->input('components');

        // Save permissions
        $existingPermissions = PermissionRole::where('role_id', $role_id)->pluck('permission_id')->toArray();
        $permissionsToDelete = array_diff($existingPermissions, $permissions);
        PermissionRole::where('role_id', $role_id)->whereIn('permission_id', $permissionsToDelete)->delete();

        foreach ($permissions as $permission_id) {
            PermissionRole::updateOrCreate(
                ['role_id' => $role_id, 'permission_id' => $permission_id],
                ['role_id' => $role_id, 'permission_id' => $permission_id]
            );
        }

        // Save components
        $existingComponents = RoleComponent::where('role_id', $role_id)->pluck('component_id')->toArray();
        $componentsToDelete = array_diff($existingComponents, $components);
        RoleComponent::where('role_id', $role_id)->whereIn('component_id', $componentsToDelete)->delete();

        foreach ($components as $component_id) {
            RoleComponent::updateOrCreate(
                ['role_id' => $role_id, 'component_id' => $component_id],
                ['role_id' => $role_id, 'component_id' => $component_id, 'can_view' => true]
            );
        }

        return response()->json(['message' => 'Permissions and components saved successfully!']);
    }

    public function getPermissions($role_id)
    {
        $permissions = PermissionRole::where('role_id', $role_id)->get();
        return response()->json($permissions);
    }

    public function getComponents($role_id)
    {
        $components = RoleComponent::where('role_id', $role_id)->get();
        return response()->json($components);
    }
}