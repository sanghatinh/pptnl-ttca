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

    // public function register(Request $request)
    // {
    //     // created a new user with the request data = user_name, email, password. use try catch 
    //     try {
    //         $user = new User();
    //         $user->username = $request->username;
    //         $user->password = $request->password;
    //         $user->email = $request->email;
           
    //         $user->full_name = $request->full_name;
    //         $user->phone = $request->phone;
    //         $user->position = $request->position;
    //         $user->department = $request->department;

    //         $user->save();

    //         $success = true;
    //         $message = "ບັນທຶກຂໍ້ມູນສຳເລັດ!";
            
    //     } catch (\Illuminate\Database\QueryException $ex) {
    //         $success = false;
    //         $message = $ex->getMessage();
            
    //     }

    //     $response = [
    //         'success' => $success,
    //         'message' => $message
    //     ];

    //     return response()->json($response);

    // }

    public function login(Request $request)
    {
        try {
            $credentials = [
                'username' => $request->username,
                'password' => $request->password
            ];
    
            if ($request->remember_me) {
                JWTAuth::factory()->setTTL(60 * 24 * 7);
            }
    
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
    
            $user = auth()->user();
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive'
                ], 403);
            }
    
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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

            try {
                JWTAuth::invalidate($token);
            } catch (TokenExpiredException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired'
                ], 401);
            }

            $success = true;
            $message = "ອອກຈາກລະບົບສຳເລັດ!";

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
                'ma_nhan_vien' => $user->ma_nhan_vien
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
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
    ]);

    try {
        // ตรวจสอบไฟล์ก่อน
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

        // ใช้ CloudinaryService
        $cloudinaryService = new CloudinaryService();
        
        // ลองใช้ unsigned upload ก่อน (ต้องสร้าง upload preset: ml_default ใน Cloudinary console)
        $result = $cloudinaryService->uploadImageUnsigned($file, 'ml_default');
        
        // ถ้าไม่สำเร็จ ลองใช้ signed upload
        if (!$result['success']) {
            $result = $cloudinaryService->uploadImageSimple($file, 'users/temp');
        }
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'image_url' => $result['secure_url'],
                'public_id' => $result['public_id']
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
        $user = User::find($id);
        $user->password = ''; // เคลียร์ฟิลด์รหัสผ่านเพื่อความปลอดภัย
        return response()->json($user);
    } catch (\Exception $e) {
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
    ]);

    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->username   = $request->username;
        $user->full_name  = $request->full_name;
        $user->email      = $request->email;
        $user->phone      = $request->phone;
        $user->position   = $request->position;
        $user->station    = $request->station;
        $user->role_id    = $request->role_id;
        $user->status     = $request->status;
        $user->ma_nhan_vien = $request->ma_nhan_vien;

        // Update password only if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Sync join table user_role
        $user->roles()->sync([$request->role_id]);

        return response()->json(['message' => 'success'], 200);
    } catch (\Illuminate\Database\QueryException $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}


//Delete user
public function deleteuser($id)
{
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Detach associated roles in user_role join table
        $user->roles()->detach();
        $user->delete();

        return response()->json(['message' => 'success'], 200);
    } catch (\Exception $e) {
        \Log::error('Error deleting user: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
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
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'id' => $user->id,
            'position' => $user->position,
            'station' => $user->station,
            'ma_nhan_vien' => $user->ma_nhan_vien,
            'full_name' => $user->full_name
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in getUserInfo: ' . $e->getMessage());
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
        
        // Password verification
        $passwordMatches = false;
        
        // Try bcrypt verification first
        try {
            $passwordMatches = Hash::check($password, $farmer->password);
        } catch (\Exception $e) {
            // If bcrypt verification fails, try direct comparison
            $passwordMatches = ($password === $farmer->password);
            
            if ($passwordMatches) {
                $farmer->password = Hash::make($password);
                $farmer->save();
            }
        }
        
        if (!$passwordMatches) {
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





}