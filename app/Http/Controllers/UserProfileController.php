<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Services\CloudinaryService;

class UserProfileController extends Controller
{
    /**
     * Get current user's profile data
     */
    public function getMyProfile(Request $request)
    {
        try {
            // ดึง user ID จาก JWT token
            $token = $request->bearerToken();
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            
            // ดึงข้อมูล user พร้อม joins เพื่อ map ข้อมูล
            $userQuery = DB::table('users as u')
                ->leftJoin('listposition as lp', 'u.position', '=', 'lp.id_position')
                ->leftJoin('liststation as ls', 'u.station', '=', 'ls.ma_don_vi')
                ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
                ->where('u.id', $userId)
                ->select([
                    'u.*',
                    'lp.position as position_name',
                    'lp.department',
                    'ls.Name as station_name',
                    'r.name as role_name'
                ])
                ->first();
            
            if (!$userQuery) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // ใช้ CloudinaryService สำหรับรูปภาพ
            $imageUrl = null;
            if ($userQuery->image) {
                $cloudinaryService = new CloudinaryService();
                $imageUrl = $cloudinaryService->getTransformationUrl($userQuery->image, [
                    'width' => 200,
                    'height' => 200,
                    'crop' => 'fill',
                    'gravity' => 'face',
                    'quality' => 'auto:good'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $userQuery->id,
                    'username' => $userQuery->username,
                    'full_name' => $userQuery->full_name,
                    'ma_nhan_vien' => $userQuery->ma_nhan_vien,
                    'position' => $userQuery->position,          // department code
                    'position_name' => $userQuery->position_name, // แสดงชื่อตำแหน่งที่ map แล้ว
                    'station' => $userQuery->station,            // station code
                    'station_name' => $userQuery->station_name,  // แสดงชื่อสถานีที่ map แล้ว
                    'email' => $userQuery->email,
                    'phone' => $userQuery->phone,
                    'role_id' => $userQuery->role_id,           // role id
                    'role_name' => $userQuery->role_name,       // แสดงชื่อบทบาทที่ map แล้ว
                    'status' => $userQuery->status,
                    'image' => $userQuery->image,
                    'image_public_id' => $userQuery->image,
                    'image_url' => $imageUrl,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update current user's profile
     */
    public function updateMyProfile(Request $request)
    {
        try {
            // ดึง user ID จาก JWT token
            $token = $request->bearerToken();
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Validation rules - เฉพาะฟิลด์ที่แก้ไขได้
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email|max:255|unique:users,email,' . $userId,
                'phone' => 'nullable|string|max:20',
                'new_password' => 'nullable|string|min:6',
                'confirm_password' => 'nullable|string|same:new_password|required_with:new_password',
                'image' => 'nullable|string', // สำหรับ public_id จาก Cloudinary
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            try {
                // อัปเดตข้อมูลที่แก้ไขได้เท่านั้น
                if ($request->filled('email')) {
                    $user->email = $request->email;
                }
                
                if ($request->filled('phone')) {
                    $user->phone = $request->phone;
                }
                
                // อัปเดตรหัสผ่าน (ถ้ามีการส่งมา)
                if ($request->filled('new_password')) {
                    $user->password = Hash::make($request->new_password);
                }
                
                // อัปเดตรูปภาพ (ถ้ามี)
                if ($request->filled('image')) {
                    // ลบรูปเก่าจาก Cloudinary (ถ้ามี)
                    if ($user->image) {
                        try {
                            $cloudinaryService = new CloudinaryService();
                            $cloudinaryService->deleteImageEnhanced($user->image);
                        } catch (\Exception $e) {
                            // ไม่ต้อง throw error ถ้าลบไม่ได้
                        }
                    }
                    
                    $user->image = $request->image;
                }
                
                $user->save();
                
                DB::commit();
                
                // ดึงข้อมูลที่อัปเดตแล้วพร้อม mapping เพื่อส่งกลับ
                $updatedUserQuery = DB::table('users as u')
                    ->leftJoin('listposition as lp', 'u.position', '=', 'lp.department')
                    ->leftJoin('liststation as ls', 'u.station', '=', 'ls.ma_don_vi')
                    ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
                    ->where('u.id', $userId)
                    ->select([
                        'u.*',
                        'lp.position as position_name',
                        'ls.Name as station_name',
                        'r.name as role_name'
                    ])
                    ->first();
                
                // สร้าง image_url สำหรับ response
                $imageUrl = null;
                if ($updatedUserQuery->image) {
                    $cloudinaryService = new CloudinaryService();
                    $imageUrl = $cloudinaryService->getTransformationUrl($updatedUserQuery->image, [
                        'width' => 200,
                        'height' => 200,
                        'crop' => 'fill',
                        'gravity' => 'face',
                        'quality' => 'auto:good'
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => [
                        'id' => $updatedUserQuery->id,
                        'username' => $updatedUserQuery->username,
                        'full_name' => $updatedUserQuery->full_name,
                        'ma_nhan_vien' => $updatedUserQuery->ma_nhan_vien,
                        'position' => $updatedUserQuery->position,
                        'position_name' => $updatedUserQuery->position_name,
                        'station' => $updatedUserQuery->station,
                        'station_name' => $updatedUserQuery->station_name,
                        'email' => $updatedUserQuery->email,
                        'phone' => $updatedUserQuery->phone,
                        'role_id' => $updatedUserQuery->role_id,
                        'role_name' => $updatedUserQuery->role_name,
                        'status' => $updatedUserQuery->status,
                        'image' => $updatedUserQuery->image,
                        'image_public_id' => $updatedUserQuery->image,
                        'image_url' => $imageUrl,
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete current user's image
     */
    public function deleteMyImage(Request $request)
    {
        try {
            // ดึง user ID จาก JWT token
            $token = $request->bearerToken();
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            if (!$user->image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image to delete'
                ], 400);
            }
            
            // ลบรูปภาพจาก Cloudinary
            try {
                $cloudinaryService = new CloudinaryService();
                $deleteResult = $cloudinaryService->deleteImageEnhanced($user->image);
                
                if (!$deleteResult['success']) {
                    // Log warning แต่ยังดำเนินการลบในฐานข้อมูลต่อ
                    \Log::warning('Failed to delete image from Cloudinary', [
                        'user_id' => $userId,
                        'public_id' => $user->image,
                        'error' => $deleteResult['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                // Log error แต่ไม่หยุดการดำเนินงาน
                \Log::error('Exception while deleting image from Cloudinary: ' . $e->getMessage());
            }
            
            // ลบข้อมูลรูปภาพจากฐานข้อมูล
            $user->image = null;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}