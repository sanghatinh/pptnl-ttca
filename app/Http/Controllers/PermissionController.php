<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions
     */
    public function index()
    {
        try {
            $permissions = DB::table('permissions')
                ->select('id', 'name', 'created_at', 'updated_at')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($permissions);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch permissions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $request->name,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $permission = DB::table('permissions')->where('id', $permissionId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create permission',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified permission
     */
    public function show($id)
    {
        try {
            $permission = DB::table('permissions')->where('id', $id)->first();

            if (!$permission) {
                return response()->json([
                    'error' => 'Permission not found'
                ], 404);
            }

            return response()->json($permission);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch permission',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, $id)
    {
        try {
            $permission = DB::table('permissions')->where('id', $id)->first();

            if (!$permission) {
                return response()->json([
                    'error' => 'Permission not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:permissions,name,' . $id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            DB::table('permissions')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'updated_at' => now()
                ]);

            $updatedPermission = DB::table('permissions')->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully',
                'data' => $updatedPermission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update permission',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified permission
     */
    public function destroy($id)
    {
        try {
            $permission = DB::table('permissions')->where('id', $id)->first();

            if (!$permission) {
                return response()->json([
                    'error' => 'Permission not found'
                ], 404);
            }

            // Check if permission is being used in permission_role table
            $usageCount = DB::table('permission_role')
                ->where('permission_id', $id)
                ->count();

            if ($usageCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete permission',
                    'message' => 'This permission is currently assigned to one or more roles'
                ], 409);
            }

            DB::table('permissions')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete permission',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}