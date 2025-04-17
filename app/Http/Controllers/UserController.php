<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;


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
        'password'   => 'required',
        'full_name'  => 'required',
        'email'      => 'nullable|email|unique:users',
        'phone'      => 'nullable|string',
        'position'   => 'required|string',
        'station'    => 'required|string',
        'role_id'    => 'required|exists:roles,id',
        'status'     => 'required|in:active,inactive',
        'ma_nhan_vien' => 'nullable|string',
    ]);

    try {
        $user = new User();
        $user->username    = $request->username;
        $user->password    = Hash::make($request->password);
        $user->full_name   = $request->full_name;
        $user->email       = $request->email;
        $user->phone       = $request->phone;
        $user->position    = $request->position;
        $user->station     = $request->station;
        $user->role_id     = $request->role_id;
        $user->status      = $request->status;
        $user->ma_nhan_vien = $request->ma_nhan_vien;

        // Set created_user from currently logged in user
        $user->created_user = Auth::id();
        $user->save();

        // Sync join table user_role. Assumes User model defines a roles() relationship.
        $user->roles()->sync([$request->role_id]);

        return response()->json(['message' => 'success'], 201);
    } catch (\Illuminate\Database\QueryException $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
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
public function getUserPermissions()
{
    try {
        $user = Auth::user();
        $permissions = [];

        // Attempt to load roles from the relationship (user_role)
        $userRoles = $user->roles;

        // If no roles are found through user_role but role_id exists, load that role
        if ($userRoles->isEmpty() && $user->role_id) {
            $role = \App\Models\Role::with('permissions')->find($user->role_id);
            if ($role) {
                $userRoles = collect([$role]);
            }
        }

        // Loop through each role and collect permission names
        foreach ($userRoles as $role) {
            // Ensure permissions relationship is loaded
            $rolePermissions = $role->permissions;
            foreach ($rolePermissions as $permission) {
                $permissions[] = $permission->name;
            }
        }

        $permissions = array_values(array_unique($permissions));
        return response()->json($permissions);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
//เพิ่มฟังก์ชันใน UserController เพื่อดึงข้อมูล components
public function getUserComponents()
{
    try {
        $user = Auth::user();
        $components = [];
        
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
        
        $components = array_values(array_unique($components));
        return response()->json($components);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}







}