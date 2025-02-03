<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use JWTAuth;
use Illuminate\Support\Facades\Auth;

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
            JWTAuth::invalidate($token);

            $success = true;
            $message = "ອອກຈາກລະບົບສຳເລັດ!";

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
}
