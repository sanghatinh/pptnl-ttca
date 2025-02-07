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

    public function register(Request $request)
    {
        // created a new user with the request data = user_name, email, password. use try catch 
        try {
            $user = new User();
            $user->username = $request->username;
            $user->password = $request->password;
            $user->email = $request->email;
           
            $user->full_name = $request->full_name;
            $user->phone = $request->phone;
            $user->position = $request->position;
            $user->department = $request->department;

            $user->save();

            $success = true;
            $message = "ບັນທຶກຂໍ້ມູນສຳເລັດ!";
            
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
            $message = $ex->getMessage();
            
        }

        $response = [
            'success' => $success,
            'message' => $message
        ];

        return response()->json($response);

    }

public function login(Request $request)
{
    try {
        $credentials = [
            'username' => $request->username,
            'password' => $request->password
        ];

        if($request->remember_me) {
            JWTAuth::factory()->setTTL(60*24*7);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'ຊື່ຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ'
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'ເຂົ້າສູ່ລະບົບສຳເລັດ',
            'token' => $token,
            'user' => $user
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage()
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
            $positions = DB::table('listposition')->select('position')->get();
            return response()->json($positions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStations()
    {
        try {
            $stations = DB::table('liststation')->select('name')->get();
            return response()->json($stations);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
    try {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required',
            'full_name' => 'required',
            'email' => 'nullable|email|unique:users',
            'phone' => 'nullable|string',
            'position' => 'required|string',
            'station' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = new User();
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->position = $request->position;
        $user->station = $request->station;
        $user->role_id = $request->role_id;
        $user->status = $request->status;
        $user->save();

        return response()->json(['message' => 'success'], 201);
    } catch (\Illuminate\Database\QueryException $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function edituser($id)
{
    try {
        $user = User::find($id);
        return response()->json($user);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function updateuser(Request $request, $id)
{
    try {
        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'full_name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string',
            'position' => 'required|string',
            'station' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::find($id);
        $user->username = $request->username;
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->position = $request->position;
        $user->station = $request->station;
        $user->role_id = $request->role_id;
        $user->status = $request->status;
        $user->save();

        return response()->json(['message' => 'success'], 200);
    } catch (\Illuminate\Database\QueryException $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }

}

}