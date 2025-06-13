<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        try {
            $roles = DB::table('roles')
                ->select('id', 'name', 'created_at', 'updated_at')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($roles);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch roles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $roleId = DB::table('roles')->insertGetId([
                'name' => $request->name,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $role = DB::table('roles')->where('id', $roleId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        try {
            $role = DB::table('roles')->where('id', $id)->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Role not found'
                ], 404);
            }

            return response()->json($role);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        try {
            $role = DB::table('roles')->where('id', $id)->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Role not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name,' . $id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            DB::table('roles')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'updated_at' => now()
                ]);

            $updatedRole = DB::table('roles')->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $updatedRole
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        try {
            $role = DB::table('roles')->where('id', $id)->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Role not found'
                ], 404);
            }

            // Check if role is Super Admin (ป้องกันการลบ Super Admin)
            if ($role->name === 'Super Admin') {
                return response()->json([
                    'error' => 'Cannot delete Super Admin role',
                    'message' => 'Super Admin role cannot be deleted for security reasons'
                ], 403);
            }

            // Check if role is being used in permission_role table
            $permissionUsageCount = DB::table('permission_role')
                ->where('role_id', $id)
                ->count();

            // Check if role is being used in component_role table
            $componentUsageCount = DB::table('component_role')
                ->where('role_id', $id)
                ->count();

            // Check if role is being used in users table
            $userUsageCount = DB::table('users')
                ->where('role', $id)
                ->count();

            if ($permissionUsageCount > 0 || $componentUsageCount > 0 || $userUsageCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete role',
                    'message' => 'This role is currently assigned to users or has permissions/components assigned'
                ], 409);
            }

            DB::table('roles')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete role',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}