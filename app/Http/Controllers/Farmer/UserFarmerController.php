<?php
// filepath: f:\Webpoject\TTCA_PTNL\ttca_ptnl\app\Http\Controllers\Farmer\UserFarmerController.php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Farmer\UserFarmer;
use App\Models\Quanlytaichinh\Banking;

class UserFarmerController extends Controller
{
    /**
     * Get all farmer users with related data
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('user_farmer as uf')
                ->leftJoin('roles as r', 'uf.role_id', '=', 'r.id')
                ->leftJoin('banking as b', 'uf.ngan_hang', '=', 'b.code_banking')
                ->select(
                    'uf.id',
                   
                    'uf.tram',
                    'uf.supplier_number',
                    'uf.ma_kh_ca_nhan',
                    'uf.khach_hang_ca_nhan',
                    'uf.ma_kh_doanh_nghiep',
                    'uf.khach_hang_doanh_nghiep',
                    'uf.phone',
                   
                    'uf.dia_chi_thuong_tru',
                    'uf.chu_tai_khoan',
                    'uf.ngan_hang as bank_code',
                    'b.name_banking as bank_name',
                    'uf.so_tai_khoan',
                    'uf.role_id',
                    'r.name as role_name',
                    'uf.status',
                    'uf.created_at',
                    'uf.updated_at'
                );

            // Apply filters if provided
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                   $q->where('uf.khach_hang_ca_nhan', 'like', "%{$search}%")
                      ->orWhere('uf.khach_hang_doanh_nghiep', 'like', "%{$search}%")
                      ->orWhere('uf.supplier_number', 'like', "%{$search}%")
                      ->orWhere('uf.ma_kh_ca_nhan', 'like', "%{$search}%")
                      ->orWhere('uf.ma_kh_doanh_nghiep', 'like', "%{$search}%")
                      ->orWhere('uf.phone', 'like', "%{$search}%");
                    
                    
                });
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('uf.status', $request->status);
            }

            // Filter by role
            if ($request->has('role_id') && !empty($request->role_id)) {
                $query->where('uf.role_id', $request->role_id);
            }

            // Filter by station
            if ($request->has('tram') && !empty($request->tram)) {
                $query->where('uf.tram', $request->tram);
            }

            $farmers = $query->orderBy('uf.id', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $farmers
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching farmer users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching farmer users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unique values for filters
     */
    public function getFilterOptions()
    {
        try {
            // Get unique stations
            $stations = DB::table('user_farmer')
                ->select('tram')
                ->whereNotNull('tram')
                ->where('tram', '!=', '')
                ->distinct()
                ->orderBy('tram')
                ->pluck('tram');

            // Get roles
            $roles = DB::table('roles')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // Get banks
            $banks = DB::table('banking')
                ->select('code_banking', 'name_banking')
                ->orderBy('name_banking')
                ->get();

            // Get status options
            $statuses = ['active', 'inactive'];

            return response()->json([
                'success' => true,
                'data' => [
                    'stations' => $stations,
                    'roles' => $roles,
                    'banks' => $banks,
                    'statuses' => $statuses
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching filter options: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching filter options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific farmer user
     */
    public function show($id)
    {
        try {
            $farmer = DB::table('user_farmer as uf')
                ->leftJoin('roles as r', 'uf.role_id', '=', 'r.id')
                ->leftJoin('banking as b', 'uf.ngan_hang', '=', 'b.code_banking')
                ->select(
                    'uf.*',
                    'r.name as role_name',
                    'b.name_banking as bank_name'
                )
                ->where('uf.id', $id)
                ->first();

            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer user not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $farmer
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching farmer user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update farmer user
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'tram' => 'nullable|string|max:255',
                'supplier_number' => 'nullable|string|max:255',
                'ma_kh_ca_nhan' => 'nullable|string|max:255',
                'khach_hang_ca_nhan' => 'nullable|string|max:255',
                'ma_kh_doanh_nghiep' => 'nullable|string|max:255',
                'khach_hang_doanh_nghiep' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'dia_chi_thuong_tru' => 'nullable|string',
                'chu_tai_khoan' => 'nullable|string|max:255',
                'ngan_hang' => 'nullable|string|max:255',
                'so_tai_khoan' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer|exists:roles,id',
                'status' => 'required|in:active,inactive'
            ]);

            $farmer = UserFarmer::find($id);
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer user not found'
                ], 404);
            }

            $farmer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Farmer user updated successfully',
                'data' => $farmer
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating farmer user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete farmer user
     */
    public function destroy($id)
    {
        try {
            $farmer = UserFarmer::find($id);
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer user not found'
                ], 404);
            }

            $farmer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Farmer user deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting farmer user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new farmer user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'tram' => 'nullable|string|max:255',
                'supplier_number' => 'nullable|string|max:255|unique:user_farmer,supplier_number',
                'ma_kh_ca_nhan' => 'nullable|string|max:255|unique:user_farmer,ma_kh_ca_nhan',
                'khach_hang_ca_nhan' => 'nullable|string|max:255',
                'ma_kh_doanh_nghiep' => 'nullable|string|max:255|unique:user_farmer,ma_kh_doanh_nghiep',
                'khach_hang_doanh_nghiep' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:user_farmer,email',
                'dia_chi_thuong_tru' => 'nullable|string',
                'chu_tai_khoan' => 'nullable|string|max:255',
                'ngan_hang' => 'nullable|string|max:255',
                'so_tai_khoan' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer|exists:roles,id',
                'status' => 'required|in:active,inactive',
                'password' => 'required|string|min:6'
            ]);

            // Hash password
            $validated['password'] = bcrypt($validated['password']);

            $farmer = UserFarmer::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Farmer user created successfully',
                'data' => $farmer
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating farmer user: ' . $e->getMessage()
            ], 500);
        }
    }
}