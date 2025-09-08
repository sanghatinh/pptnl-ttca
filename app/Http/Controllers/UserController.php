<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Farmer\UserFarmer;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Validator; 
use App\Services\CloudinaryService; 
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{

    
public function login(Request $request)
{
    try {
        $credentials = [
            'username' => $request->username,
            'password' => $request->password
        ];

        $deviceId = $request->input('device_id');
        $deviceType = $request->input('device_type'); // "pc" หรือ "mobile"
        $deviceIp = $request->input('device_ip');

          // Nếu device_id hoặc device_type là null, hoặc device_ip là null thì vẫn cho login
        if ((is_null($deviceId) || is_null($deviceType)) && is_null($deviceIp)) {
            // Cho phép login, không trả về lỗi
        } else {
            if (!$deviceId || !$deviceType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập device_id và device_type'
                ], 400);
            }
        }

        // ตรวจสอบ device_id_pc และ last_login_ip ซ้ำกับ user อื่น
        if ($deviceType === 'pc') {
            $conflictUser = User::where(function($q) use ($deviceId, $deviceIp) {
                    $q->where('device_id_pc', $deviceId)
                      ->orWhere('last_login_ip', $deviceIp);
                })
                ->where('status', 'active')
                ->where('username', '!=', $request->username)
                ->first();

            if ($conflictUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Máy này đã được sử dụng bởi user khác'
                ], 409);
            }
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Sai tên đăng nhập hoặc mật khẩu'
            ], 401);
        }

        $user = auth()->user();
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn không hoạt động. Vui lòng liên hệ quản trị viên.'
            ], 403);
        }

        // บันทึก device_id และ IP ตามประเภท
        if ($deviceType === 'pc') {
            if (empty($user->device_id_pc)) {
                $user->device_id_pc = $deviceId;
                $user->last_login_ip = $deviceIp;
                $user->last_login_at = now();
                $user->save();
            } else {
                if ($user->device_id_pc !== $deviceId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tài khoản đã được đăng nhập trên PC/Notebook khác'
                    ], 409);
                } else {
                    $user->last_login_ip = $deviceIp;
                    $user->last_login_at = now();
                    $user->save();
                }
            }
        } else { // mobile
            if (empty($user->device_id_mobile)) {
                $user->device_id_mobile = $deviceId;
                $user->last_login_ip = $deviceIp;
                $user->last_login_at = now();
                $user->save();
            } else {
                if ($user->device_id_mobile !== $deviceId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tài khoản này đã được đăng nhập trên điện thoại khác'
                    ], 409);
                } else {
                    $user->last_login_ip = $deviceIp;
                    $user->last_login_at = now();
                    $user->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ], 500);
    }
}


public function logout(Request $request)
{
    try {
        $token = JWTAuth::getToken();
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 401);
        }

        // ตรวจสอบว่าต้องการ logout ทุกอุปกรณ์หรือไม่
        $logoutAll = $request->input('logout_all_devices', false);

        if ($logoutAll) {
            // ลบ device_id ออกจาก user
            $user = Auth::user();
            if ($user) {
                $user->device_id = null;
                $user->save();
            }
        }

        try {
            JWTAuth::invalidate($token);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired'
            ], 401);
        }

        $success = true;
        $message = $logoutAll ? "Logout from all devices success!" : "Logout success!";

    } catch (\Exception $ex) {
        $success = false;
        $message = $ex->getMessage();
    }

    $response = [
        'success' => $success,
        'message' => $message
    ];

    return response()->json($response);
}

    //ดึงข้อมูลจากตาราง listposition และ liststation
    public function getPositions()
    {
        try {
            $positions = DB::table('listposition')
                ->select('id_position', 'position', 'department')
                ->get();
            return response()->json($positions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStations() {
        $stations = DB::table('liststation')
            ->select('id', 'Name', 'ma_don_vi')
            ->get();
        
        return response()->json($stations);
    }
   
//เพิ่มฟังก์ชันใน UserController เพื่อดึงข้อมูล roles
public function getRoles()
{
    try {
        $roles = DB::table('roles')->select('id', 'name')->get();
        return response()->json($roles);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
//ดืงข้อมูลจากตาราง users


public function getUsers()
{
    try {
        $users = User::with('roles')->get();
        return response()->json($users);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

// เพิ่มฟังก์ชันใน UserController Add new user

public function addnewuser(Request $request)
{
    $request->validate([
        'username'   => 'required|unique:users',
        'password'   => 'required|min:6',
        'full_name'  => 'required',
        'email'      => 'nullable|email|unique:users',
        'phone'      => 'nullable|string',
        'position'   => 'required|string',
        'station'    => 'required|string',
        'role_id'    => 'required|exists:roles,id',
        'status'     => 'required|string',
        'ma_nhan_vien' => 'nullable|string',
        'manv_ttca'  => 'nullable|string',
        'image'      => 'nullable|string' // เปลี่ยนเป็น string สำหรับ public_id
    ]);

    DB::beginTransaction();

    try {
        $user = new User();
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->position = $request->position;
        $user->station = $request->station;
        $user->status = $request->status;
        $user->ma_nhan_vien = $request->ma_nhan_vien;
         $user->manv_ttca = $request->manv_ttca;
        $user->role_id = $request->role_id;

        // ถ้ามี image public_id ให้บันทึก
        if ($request->filled('image')) {
            $user->image = $request->image;
            Log::info('User image set from public_id', [
                'public_id' => $request->image
            ]);
        }

        // Set created_user from currently logged in user
        $user->created_user = Auth::id();
        $user->save();

        // Sync join table user_role
        $user->roles()->sync([$request->role_id]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'success',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'image' => $user->image,
                'email' => $user->email,
                'phone' => $user->phone,
                'position' => $user->position,
                'station' => $user->station,
                'status' => $user->status,
                'role_id' => $user->role_id,
                'ma_nhan_vien' => $user->ma_nhan_vien,
                'manv_ttca' => $user->manv_ttca
            ]
        ], 201);

    } catch (\Illuminate\Database\QueryException $ex) {
        DB::rollBack();
        Log::error('Database error in addnewuser: ' . $ex->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Database error occurred',
            'error' => $ex->getMessage()
        ], 500);
    } catch (\Exception $ex) {
        DB::rollBack();
        Log::error('General error in addnewuser: ' . $ex->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while creating user',
            'error' => $ex->getMessage()
        ], 500);
    }
}

// แก้ไข method uploadUserImage เช่นกัน

public function uploadUserImage(Request $request)
{
    Log::info('Upload user image request received', [
        'has_file' => $request->hasFile('image'),
        'file_size' => $request->hasFile('image') ? $request->file('image')->getSize() : 0
    ]);

    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240' // 10MB max
    ]);

    try {
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

        $cloudinaryService = new CloudinaryService();
        
        // ใช้ uploadImageOptimized แทน
        $result = $cloudinaryService->uploadImageOptimized($file, [
            'upload_preset' => 'ml_default',
            'folder' => 'users',
            'quality' => 'auto:good',
            'format' => 'auto'
        ]);
        
        if ($result['success']) {
            Log::info('Image upload successful', [
                'public_id' => $result['public_id'],
                'upload_time' => $result['upload_time'] ?? 0
            ]);
            
            return response()->json([
                'success' => true,
                'image_url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'upload_time' => $result['upload_time'] ?? 0
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Upload failed',
                'error' => $result['error'] ?? 'Unknown error'
            ], 500);
        }
        
    } catch (\Exception $e) {
        Log::error('Upload user image exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload image',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function edituser($id)
{
    try {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        // สร้าง Cloudinary URL สำหรับรูปภาพ
        $imageUrl = null;
        if ($user->image) {
            $cloudinaryService = new CloudinaryService();
            $imageUrl = $cloudinaryService->getTransformationUrl($user->image, [
                'width' => 200,
                'height' => 200,
                'crop' => 'fill',
                'gravity' => 'face',
                'quality' => 'auto:good'
            ]);
        }
        
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'position' => $user->position,
            'station' => $user->station,
            'role_id' => $user->role_id,
            'status' => $user->status,
            'ma_nhan_vien' => $user->ma_nhan_vien,
             'manv_ttca' => $user->manv_ttca, // เพิ่มตรงนี้
            'image' => $imageUrl,
            'image_public_id' => $user->image // เก็บ public_id ไว้สำหรับการลบ
        ];
        
        return response()->json($userData);
        
    } catch (\Exception $e) {
        Log::error('Error in edituser: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function updateuser(Request $request, $id)
{
    $request->validate([
        'username'   => 'required',
        'full_name'  => 'required',
        'email'      => 'nullable|email|unique:users,email,'.$id,
        'phone'      => 'nullable|string',
        'position'   => 'required|string',
        'station'    => 'required|string',
        'role_id'    => 'required|exists:roles,id',
        'status'     => 'required|in:active,inactive',
        'ma_nhan_vien' => 'nullable|string',
        'manv_ttca'  => 'nullable|string',
        'new_password' => 'nullable|string|min:6',
        'confirm_password' => 'nullable|string|same:new_password|required_with:new_password',
        'image' => 'nullable|string', // สำหรับ public_id จาก Cloudinary
    ]);

    DB::beginTransaction();

    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // ตรวจสอบและอัปเดตรหัสผ่าน (ถ้ามีการส่งมา)
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
            Log::info('Password updated for user', ['user_id' => $id]);
        }

        // อัปเดตข้อมูลทั่วไป
        $user->username   = $request->username;
        $user->full_name  = $request->full_name;
        $user->email      = $request->email;
        $user->phone      = $request->phone;
        $user->position   = $request->position;
        $user->station    = $request->station;
        $user->role_id    = $request->role_id;
        $user->status     = $request->status;
        $user->ma_nhan_vien = $request->ma_nhan_vien;
        $user->manv_ttca  = $request->manv_ttca;

        // อัปเดตรูปภาพ (ถ้ามี)
        if ($request->filled('image')) {
            // ลบรูปเก่าจาก Cloudinary (ถ้ามี)
            if ($user->image) {
                try {
                    $cloudinaryService = new CloudinaryService();
                    $cloudinaryService->deleteImageEnhanced($user->image);
                    Log::info('Old image deleted from Cloudinary', ['old_image' => $user->image]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old image from Cloudinary: ' . $e->getMessage());
                }
            }
            
            $user->image = $request->image;
            Log::info('User image updated', ['user_id' => $id, 'new_image' => $request->image]);
        }

        $user->save();

        // Sync join table user_role
        $user->roles()->sync([$request->role_id]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'success',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'image' => $user->image,
                'email' => $user->email,
                'phone' => $user->phone,
                'position' => $user->position,
                'station' => $user->station,
                'status' => $user->status,
                'role_id' => $user->role_id,
                'ma_nhan_vien' => $user->ma_nhan_vien,
                'manv_ttca' => $user->manv_ttca
            ]
        ], 200);

    } catch (\Illuminate\Database\QueryException $ex) {
        DB::rollBack();
        Log::error('Database error in updateuser: ' . $ex->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Database error occurred',
            'error' => $ex->getMessage()
        ], 500);
    } catch (\Exception $ex) {
        DB::rollBack();
        Log::error('General error in updateuser: ' . $ex->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating user',
            'error' => $ex->getMessage()
        ], 500);
    }
}


//Delete user
public function deleteuser($id)
{
    DB::beginTransaction();
    
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // ตรวจสอบไม่ให้ลบตัวเอง
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        // ลบรูปภาพจาก Cloudinary (ถ้ามี)
        if ($user->image) {
            try {
                $cloudinaryService = new CloudinaryService();
                $deleteResult = $cloudinaryService->deleteImageEnhanced($user->image);
                
                if ($deleteResult['success']) {
                    Log::info('User image deleted from Cloudinary successfully', [
                        'user_id' => $id,
                        'public_id' => $user->image
                    ]);
                } else {
                    Log::warning('Failed to delete user image from Cloudinary', [
                        'user_id' => $id,
                        'public_id' => $user->image,
                        'error' => $deleteResult['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Exception while deleting user image from Cloudinary: ' . $e->getMessage());
            }
        }

        // Detach associated roles in user_role join table
        $user->roles()->detach();
        
        // Delete user record
        $user->delete();

        DB::commit();

        Log::info('User deleted successfully', ['user_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ], 200);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting user: ' . $e->getMessage(), [
            'user_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while deleting user',
            'error' => $e->getMessage()
        ], 500);
    }
}

// เพิ่มฟังก์ชันลบรูปภาพของ user
public function deleteUserImage(Request $request, $id)
{
    DB::beginTransaction();
    
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if (!$user->image) {
            return response()->json([
                'success' => false,
                'message' => 'User has no image to delete'
            ], 400);
        }

        // ลบรูปภาพจาก Cloudinary
        $cloudinaryService = new CloudinaryService();
        $deleteResult = $cloudinaryService->deleteImageEnhanced($user->image);
        
        if ($deleteResult['success']) {
            // อัปเดต database - ลบ public_id
            $user->image = null;
            $user->save();
            
            DB::commit();
            
            Log::info('User image deleted successfully', [
                'user_id' => $id,
                'public_id' => $user->image
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } else {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image from Cloudinary',
                'error' => $deleteResult['error'] ?? 'Unknown error'
            ], 500);
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting user image: ' . $e->getMessage(), [
            'user_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while deleting image',
            'error' => $e->getMessage()
        ], 500);
    }
}

//เพิ่มฟังก์ชันใน UserController เพื่อดึงข้อมูล permissions
public function getFarmerPermissions(Request $request)
{
    try {
        $permissions = [];
        
        // ดึง token จาก header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        try {
            // ถอดรหัส token เพื่อดึงข้อมูล
            $payload = JWTAuth::setToken($token)->getPayload();
            $farmerId = $payload->get('sub'); // ดึง subject (ID) จาก token
            
            // ดึงข้อมูล farmer
            $farmer = UserFarmer::find($farmerId);
            
            if (!$farmer) {
                return response()->json(['error' => 'Farmer not found'], 404);
            }
            
            // ใช้ supplier_number จากข้อมูล farmer
            $supplierId = $farmer->supplier_number;
            
            // ดึงข้อมูล role จากตาราง userfarmer_role
            $roles = DB::table('userfarmer_role')
                ->where('supplier_number', $supplierId)
                ->pluck('role_id');
                
            // ดึงสิทธิ์ทั้งหมดของ role นั้น
            foreach ($roles as $roleId) {
                $rolePermissions = DB::table('permission_role')
                    ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->where('permission_role.role_id', $roleId)
                    ->pluck('permissions.name');
                    
                foreach ($rolePermissions as $permission) {
                    $permissions[] = $permission;
                }
            }
            
            $permissions = array_values(array_unique($permissions));
            return response()->json($permissions);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function getFarmerComponents(Request $request)
{
    try {
        $components = [];
        
        // ดึง token จาก header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        try {
            // ถอดรหัส token เพื่อดึงข้อมูล
            $payload = JWTAuth::setToken($token)->getPayload();
            $farmerId = $payload->get('sub'); // ดึง subject (ID) จาก token
            
            // ดึงข้อมูล farmer
            $farmer = UserFarmer::find($farmerId);
            
            if (!$farmer) {
                return response()->json(['error' => 'Farmer not found'], 404);
            }
            
            // ใช้ supplier_number จากข้อมูล farmer
          $supplierId = $farmer->supplier_number;
            
            // ดึงข้อมูล role จากตาราง userfarmer_role
            $roles = DB::table('userfarmer_role')
                ->where('supplier_number', $supplierId)
                ->pluck('role_id');
                
            // ดึง components ทั้งหมดของ role นั้น
            foreach ($roles as $roleId) {
                $roleComponents = DB::table('role_component')
                    ->join('components', 'components.id', '=', 'role_component.component_id')
                    ->where('role_component.role_id', $roleId)
                    ->where('role_component.can_view', 1)
                    ->pluck('components.name');
                    
                foreach ($roleComponents as $component) {
                    $components[] = $component;
                }
            }
            
            $components = array_values(array_unique($components));
            return response()->json($components);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
// ปรับปรุงฟังก์ชัน getUserPermissions
public function getUserPermissions()
{
    try {
        $user = Auth::user();
        $permissions = [];
        $userType = request()->header('X-User-Type', 'employee'); // ดึงข้อมูลประเภทผู้ใช้จาก header
        
        // ตรวจสอบประเภทผู้ใช้
        if ($userType === 'farmer') {
            // กรณีเป็น farmer
            $supplierId = request()->header('X-Supplier-Number');
            
            // ดึงข้อมูล role จากตาราง userfarmer_role
            $roles = DB::table('userfarmer_role')
                ->where('supplier_number', $supplierId)
                ->pluck('role_id');
                
            // ดึงสิทธิ์ทั้งหมดของ role นั้น
            foreach ($roles as $roleId) {
                $rolePermissions = DB::table('permission_role')
                    ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->where('permission_role.role_id', $roleId)
                    ->pluck('permissions.name');
                    
                foreach ($rolePermissions as $permission) {
                    $permissions[] = $permission;
                }
            }
        } else {
            // กรณีเป็น employee (ใช้โค้ดเดิม)
            $userRoles = $user->roles;
            
            // หากไม่มีข้อมูลใน user_role แต่มี role_id ในตาราง users ให้ดึง role เดียวจาก role_id
            if ($userRoles->isEmpty() && $user->role_id) {
                $role = \App\Models\Role::with('permissions')->find($user->role_id);
                if ($role) {
                    $userRoles = collect([$role]);
                }
            }
    
            // Loop through each role and collect permission names
            foreach ($userRoles as $role) {
                $rolePermissions = $role->permissions;
                foreach ($rolePermissions as $permission) {
                    $permissions[] = $permission->name;
                }
            }
        }

        $permissions = array_values(array_unique($permissions));
        return response()->json($permissions);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

// ปรับปรุงฟังก์ชัน getUserComponents
public function getUserComponents()
{
    try {
        $components = [];
        $userType = request()->header('X-User-Type', 'employee'); // ดึงข้อมูลประเภทผู้ใช้จาก header
        
        if ($userType === 'farmer') {
            // กรณีเป็น farmer
            $supplierId = request()->header('X-Supplier-Number');
            
            // ดึงข้อมูล role จากตาราง userfarmer_role
            $roles = DB::table('userfarmer_role')
                ->where('supplier_number', $supplierId)
                ->pluck('role_id');
                
            // ดึง components ทั้งหมดของ role นั้น
            foreach ($roles as $roleId) {
                $roleComponents = DB::table('role_component')
                    ->join('components', 'components.id', '=', 'role_component.component_id')
                    ->where('role_component.role_id', $roleId)
                    ->where('role_component.can_view', 1)
                    ->pluck('components.name');
                    
                foreach ($roleComponents as $component) {
                    $components[] = $component;
                }
            }
        } else {
            // กรณีเป็น employee (ใช้โค้ดเดิม)
            $user = Auth::user();
            
            // พยายามโหลด roles ที่เชื่อมโยงผ่านตาราง user_role
            $userRoles = $user->roles;
            
            // หากไม่มีข้อมูลใน user_role แต่มี role_id ในตาราง users ให้ดึง role เดียวจาก role_id
            if ($userRoles->isEmpty() && $user->role_id) {
                $role = \App\Models\Role::with('components')->find($user->role_id);
                if ($role) {
                    $userRoles = collect([$role]);
                }
            }
            
            // ตรวจสอบและดึง component ที่มี can_view = 1 ของแต่ละ role
            foreach ($userRoles as $role) {
                $roleComponents = $role->components()->wherePivot('can_view', 1)->get();
                foreach ($roleComponents as $component) {
                    $components[] = $component->name;
                }
            }
        }
        
        $components = array_values(array_unique($components));
        return response()->json($components);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function getUserInfo(Request $request)
{
    try {
        // ดึงข้อมูลประเภทผู้ใช้จาก header ที่ถูกตั้งค่าโดย JwtMiddleware
        $userType = $request->header('X-User-Type', 'employee');
        
        if ($userType === 'farmer') {
            // กรณีเป็น farmer - ลองดึงข้อมูลจากหลายแหล่ง
            $farmerId = $request->header('X-User-ID');
            $supplierId = $request->header('X-Supplier-Number');
            
            // ถ้าไม่มี X-User-ID ลองดึงจาก JWT token โดยตรง
            if (!$farmerId) {
                try {
                    $token = $request->bearerToken();
                    if ($token) {
                        $payload = JWTAuth::setToken($token)->getPayload();
                        $farmerId = $payload->get('sub');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error extracting farmer ID from token: ' . $e->getMessage());
                }
            }
            
            if (!$farmerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer authentication required',
                    'debug' => [
                        'user_type' => $userType,
                        'farmer_id' => $farmerId,
                        'supplier_id' => $supplierId,
                        'has_token' => $request->bearerToken() ? true : false
                    ]
                ], 401);
            }
            
            // ดึงข้อมูล farmer จาก database
            $farmer = UserFarmer::find($farmerId);
            
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer not found',
                    'debug' => [
                        'farmer_id' => $farmerId
                    ]
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'id' => $farmer->id,
                'name' => $farmer->name,
                'ma_kh_ca_nhan' => $farmer->ma_kh_ca_nhan,
                'ma_kh_doanh_nghiep' => $farmer->ma_kh_doanh_nghiep,
                'phone' => $farmer->phone,
                'email' => $farmer->email,
                'supplier_number' => $farmer->supplier_number,
                'user_type' => 'farmer'
            ]);
            
        } else {
            // กรณีเป็น employee
            $userId = $request->header('X-User-ID');
            $userPosition = $request->header('X-User-Position');
            $userStation = $request->header('X-User-Station');
            $userMaNhanVien = $request->header('X-User-Ma-Nhan-Vien');
            
            // ถ้าไม่มี X-User-ID ลองดึงจาก JWT token โดยตรง
            if (!$userId) {
                try {
                    $token = $request->bearerToken();
                    if ($token) {
                        $payload = JWTAuth::setToken($token)->getPayload();
                        $userId = $payload->get('sub');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error extracting user ID from token: ' . $e->getMessage());
                }
            }
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee authentication required',
                    'debug' => [
                        'user_type' => $userType,
                        'user_id' => $userId,
                        'has_token' => $request->bearerToken() ? true : false
                    ]
                ], 401);
            }
            
            // ดึงข้อมูล employee จาก database
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                    'debug' => [
                        'user_id' => $userId
                    ]
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'id' => $user->id,
                'username' => $user->username,
                'position' => $user->position,
                'station' => $user->station,
                'ma_nhan_vien' => $user->ma_nhan_vien,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role_id' => $user->role_id,
                'user_type' => 'employee'
            ]);
        }
        
    } catch (\Exception $e) {
        \Log::error('Error in getUserInfo: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'user_type' => $request->header('X-User-Type'),
            'user_id' => $request->header('X-User-ID'),
            'headers' => $request->headers->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving user info',
            'error' => $e->getMessage()
        ], 500);
    }
}
/**
 * Get users by IDs
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getUsersByIds(Request $request)
{
    $request->validate([
        'user_ids' => 'required|array',
        'user_ids.*' => 'integer'
    ]);
    
    try {
        $users = User::whereIn('id', $request->user_ids)
            ->select('id', 'full_name', 'email')
            ->get();
            
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching users by IDs: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error fetching user data',
            'error' => $e->getMessage()
        ], 500);
    }
}



/**
 * Farmer login method - handles authentication for farmers
 * 
 * @param Request $request
 * @return JsonResponse
 */


public function farmerLogin(Request $request)
{
    try {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp thông tin đầy đủ.'
            ], 422);
        }

        $identifier = $request->identifier;
        $password = $request->password;
        
        // Find the farmer by identifiers
        $farmer = UserFarmer::where('ma_kh_ca_nhan', $identifier)
            ->orWhere('ma_kh_doanh_nghiep', $identifier)
            ->orWhere('phone', $identifier)
            ->first();
            
        if (!$farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản nông dân với thông tin đã cung cấp.'
            ], 401);
        }
        
        // Check if account is active
        if ($farmer->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa.'
            ], 403);
        }
        
        // Password verification - เช็คแค่ plain text password
if ($password !== $farmer->password) {
    return response()->json([
        'success' => false,
        'message' => 'Mật khẩu không đúng.'
    ], 401);
}
        
        // Generate token directly with JWTAuth instead of using auth guard
        $token = JWTAuth::fromUser($farmer);
        
        // Set TTL if remember_me is true
        if ($request->remember_me) {
            JWTAuth::factory()->setTTL(60 * 24 * 7); // 7 days
        }
        
        // Prepare user data for response
        $userData = [
            'id' => $farmer->id,
            'name' => $farmer->name,
            'ma_kh_ca_nhan' => $farmer->ma_kh_ca_nhan,
            'ma_kh_doanh_nghiep' => $farmer->ma_kh_doanh_nghiep,
            'phone' => $farmer->phone,
            'email' => $farmer->email,
            'user_type' => 'farmer',
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => $userData
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Farmer login error: ' . $e->getMessage());
        // Enable detailed error reporting in development
        $errorMessage = config('app.debug') 
            ? 'Error: ' . $e->getMessage() 
            : 'Có lỗi xảy ra trong quá trình đăng nhập. Vui lòng thử lại sau.';
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
    }
}


public function getEmployeesByStation(Request $request)
{
    try {
        $station = $request->get('station');
        
        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station parameter is required'
            ], 400);
        }

        // ดึงรายชื่อพนักงานที่มี position เป็น "Nhân viên nông vụ" และอยู่ในสถานีที่เลือก
        $employees = DB::table('users as u')
            ->join('listposition as lp', 'u.position', '=', 'lp.id_position')
            ->where('u.station', $station)
            ->where('u.status', 'active')
            ->where('lp.position', 'LIKE', '%nông vụ%') // หรือใช้เงื่อนไขที่เหมาะสม
            ->select(
                'u.id',
                'u.full_name',
                'u.ma_nhan_vien',
                'u.position',
                'u.station',
                'lp.position as position_name'
            )
            ->orderBy('u.full_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching employees by station: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching employees: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * Reset device_id_pc and device_id_mobile for a user (admin only)
 */
public function resetDeviceId($id)
{
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->device_id_pc = null;
        $user->device_id_mobile = null;
        $user->last_login_ip = null;
        $user->last_login_at = null;
        $user->save();

        \Log::info('Device IDs reset by admin', ['user_id' => $id, 'admin_id' => auth()->id()]);

        return response()->json([
            'success' => true,
            'message' => 'Device IDs reset successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error('Error resetting device IDs: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to reset device IDs',
            'error' => $e->getMessage()
        ], 500);
    }
}


}