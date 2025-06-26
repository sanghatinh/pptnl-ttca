<?php


namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Farmer\UserFarmer;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth; 

class FarmerProfileController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Get farmer's own profile data
     * ใช้ข้อมูลจาก JWT token และ X-User-ID header
     */
   public function getMyProfile(Request $request)
{
    try {
        // ดึง farmer ID จาก header ที่ตั้งค่าโดย JwtMiddleware
        $farmerId = $request->header('X-User-ID');
        $userType = $request->header('X-User-Type');

        // ตรวจสอบว่าเป็น farmer และมี ID
        if ($userType !== 'farmer' || !$farmerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        // ดึงข้อมูล farmer พร้อม related data (เพิ่ม join กับ users)
        $farmer = DB::table('user_farmer as uf')
            ->leftJoin('roles as r', 'uf.role_id', '=', 'r.id')
            ->leftJoin('banking as b', 'uf.ngan_hang', '=', 'b.code_banking')
            ->leftJoin('users as u', 'uf.ma_nhan_vien', '=', 'u.ma_nhan_vien') // เพิ่มบรรทัดนี้
            ->select(
                'uf.id',
                'uf.tram',
                'uf.ma_nhan_vien',
                'uf.supplier_number',
                'uf.ma_kh_ca_nhan',
                'uf.khach_hang_ca_nhan',
                'uf.ma_kh_doanh_nghiep',
                'uf.khach_hang_doanh_nghiep',
                'uf.phone',
                'uf.dia_chi_thuong_tru',
                'uf.chu_tai_khoan',
                'uf.ngan_hang',
                'uf.so_tai_khoan',
                'uf.role_id',
                'uf.status',
                'uf.url_image',
                'r.name as role_name',
                'b.name_banking as bank_name',
                'u.full_name as employee_name', // เพิ่มบรรทัดนี้
                'u.username as employee_username' // เพิ่มบรรทัดนี้ (optional)
            )
            ->where('uf.id', $farmerId)
            ->first();

        if (!$farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer profile not found'
            ], 404);
        }

        // สร้าง Cloudinary URL สำหรับรูปภาพ (ถ้ามี)
        $imageUrl = null;
        if ($farmer->url_image) {
            $imageUrl = $this->cloudinaryService->getTransformationUrl($farmer->url_image, [
                'quality' => 'auto:good',
                'width' => 400,
                'height' => 400,
                'crop' => 'fill'
            ]);
        }

        // เตรียมข้อมูลสำหรับส่งกลับ
        $profileData = [
            'id' => $farmer->id,
            'tram' => $farmer->tram,
            'ma_nhan_vien' => $farmer->ma_nhan_vien,
            'employee_name' => $farmer->employee_name, // เพิ่มบรรทัดนี้
            'employee_username' => $farmer->employee_username, // เพิ่มบรรทัดนี้ (optional)
            'supplier_number' => $farmer->supplier_number,
            'ma_kh_ca_nhan' => $farmer->ma_kh_ca_nhan,
            'khach_hang_ca_nhan' => $farmer->khach_hang_ca_nhan,
            'ma_kh_doanh_nghiep' => $farmer->ma_kh_doanh_nghiep,
            'khach_hang_doanh_nghiep' => $farmer->khach_hang_doanh_nghiep,
            'phone' => $farmer->phone,
            'dia_chi_thuong_tru' => $farmer->dia_chi_thuong_tru,
            'chu_tai_khoan' => $farmer->chu_tai_khoan,
            'ngan_hang' => $farmer->ngan_hang,
            'so_tai_khoan' => $farmer->so_tai_khoan,
            'role_id' => $farmer->role_id,
            'status' => $farmer->status,
            'role_name' => $farmer->role_name,
            'bank_name' => $farmer->bank_name,
            'image_url' => $imageUrl,
            'url_image' => $farmer->url_image
        ];

        return response()->json([
            'success' => true,
            'data' => $profileData
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching farmer profile: ' . $e->getMessage(), [
            'farmer_id' => $request->header('X-User-ID'),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error fetching profile data'
        ], 500);
    }
}

    /**
     * Update farmer's profile data
     * อัปเดตเฉพาะข้อมูลที่ farmer สามารถแก้ไขได้
     */
    public function updateProfile(Request $request)
    {
        try {
            // ดึง farmer ID จาก header
            $farmerId = $request->header('X-User-ID');
            $userType = $request->header('X-User-Type');

            if ($userType !== 'farmer' || !$farmerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            // Validation rules - เฉพาะฟิลด์ที่ farmer สามารถแก้ไขได้
            $validator = Validator::make($request->all(), [
                'phone' => 'nullable|string|max:20',
                'dia_chi_thuong_tru' => 'nullable|string|max:500',
                'chu_tai_khoan' => 'nullable|string|max:255',
                'ngan_hang' => 'nullable|string|max:50',
                'so_tai_khoan' => 'nullable|string|max:50',
                'password' => 'nullable|string|min:6',
                'url_image' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // ค้นหา farmer
            $farmer = UserFarmer::find($farmerId);
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            // อัปเดตข้อมูลที่อนุญาต
            if ($request->has('phone')) {
                $farmer->phone = $request->phone;
            }

            if ($request->has('dia_chi_thuong_tru')) {
                $farmer->dia_chi_thuong_tru = $request->dia_chi_thuong_tru;
            }

            if ($request->has('chu_tai_khoan')) {
                $farmer->chu_tai_khoan = $request->chu_tai_khoan;
            }

            if ($request->has('ngan_hang')) {
                $farmer->ngan_hang = $request->ngan_hang;
            }

            if ($request->has('so_tai_khoan')) {
                $farmer->so_tai_khoan = $request->so_tai_khoan;
            }

            // อัปเดตรหัสผ่าน (ถ้ามี)
            if ($request->filled('password')) {
                $farmer->password = $request->password; // เก็บแบบ plain text ตาม requirement
                Log::info('Farmer password updated', ['farmer_id' => $farmerId]);
            }

            // อัปเดตรูปภาพ (ถ้ามี)
            if ($request->has('url_image')) {
                // ลบรูปเก่าจาก Cloudinary (ถ้ามี)
                if ($farmer->url_image && $farmer->url_image !== $request->url_image) {
                    try {
                        $this->cloudinaryService->deleteImageEnhanced($farmer->url_image);
                        Log::info('Old farmer image deleted', ['public_id' => $farmer->url_image]);
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete old farmer image: ' . $e->getMessage());
                    }
                }
                
                $farmer->url_image = $request->url_image;
            }

            $farmer->save();

            DB::commit();

            Log::info('Farmer profile updated successfully', [
                'farmer_id' => $farmerId,
                'updated_fields' => array_keys($request->only([
                    'phone', 'dia_chi_thuong_tru', 'chu_tai_khoan', 
                    'ngan_hang', 'so_tai_khoan', 'url_image'
                ]))
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating farmer profile: ' . $e->getMessage(), [
                'farmer_id' => $request->header('X-User-ID'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating profile'
            ], 500);
        }
    }

    /**
     * Upload farmer profile image
     */
    public function uploadImage(Request $request)
    {
        try {
            $farmerId = $request->header('X-User-ID');
            $userType = $request->header('X-User-Type');

            if ($userType !== 'farmer' || !$farmerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240' // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image file provided'
                ], 400);
            }

            $file = $request->file('image');
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file'
                ], 400);
            }

            // อัปโหลดไปยัง Cloudinary
            $result = $this->cloudinaryService->uploadImageOptimized($file, [
                'upload_preset' => 'ml_default',
                'folder' => 'farmer_profiles',
                'quality' => 'auto:good',
                'format' => 'auto',
                'transformation' => [
                    'width' => 800,
                    'height' => 800,
                    'crop' => 'limit'
                ]
            ]);

            if ($result['success']) {
                Log::info('Farmer image uploaded successfully', [
                    'farmer_id' => $farmerId,
                    'public_id' => $result['public_id'],
                    'upload_time' => $result['upload_time'] ?? 0
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'public_id' => $result['public_id'],
                    'secure_url' => $result['secure_url'],
                    'upload_time' => $result['upload_time'] ?? 0
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload image',
                    'error' => $result['error'] ?? 'Unknown error'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error uploading farmer image: ' . $e->getMessage(), [
                'farmer_id' => $request->header('X-User-ID'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete farmer profile image
     */
    public function deleteImage(Request $request)
    {
        try {
            $farmerId = $request->header('X-User-ID');
            $userType = $request->header('X-User-Type');

            if ($userType !== 'farmer' || !$farmerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            DB::beginTransaction();

            $farmer = UserFarmer::find($farmerId);
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            if (!$farmer->url_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image to delete'
                ], 400);
            }

            // ลบรูปภาพจาก Cloudinary
            $deleteResult = $this->cloudinaryService->deleteImageEnhanced($farmer->url_image);

            if ($deleteResult['success']) {
                // อัปเดต database
                $farmer->url_image = null;
                $farmer->save();

                DB::commit();

                Log::info('Farmer image deleted successfully', [
                    'farmer_id' => $farmerId,
                    'deleted_public_id' => $farmer->url_image
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete image',
                    'error' => $deleteResult['error'] ?? 'Unknown error'
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting farmer image: ' . $e->getMessage(), [
                'farmer_id' => $request->header('X-User-ID'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting image'
            ], 500);
        }
    }

    /**
     * Get banks for dropdown selection
     */
   public function getBanks(Request $request)
{
    try {
        $userType = $request->header('X-User-Type');

        if ($userType !== 'farmer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        // Use the same method as in UserFarmerController
        $banks = DB::table('banking')
            ->select('code_banking', 'name_banking')
            ->get(); // Remove the status filter for now

        return response()->json([
            'success' => true,
            'data' => $banks
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching banks for farmer: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error fetching banks data',
            'error' => $e->getMessage()
        ], 500);
    }
}
}