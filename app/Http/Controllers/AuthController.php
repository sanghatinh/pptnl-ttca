<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        return response()->json([
            'user' => $user,
            'role' => $user->role, // Assuming you have a role column in users table
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out']);
    }

    public function me()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = [
            'user' => $user,
            'role' => $user->role,
            'permissions' => $this->getPermissionsByRole($user->role)
        ];

        return response()->json($data);
    }

    private function getPermissionsByRole($role)
    {
        $permissions = [];
        
        switch($role) {
            case 'admin':
                $permissions = [
                    'can_manage_users' => true,
                    'can_manage_content' => true,
                    'can_view_reports' => true
                ];
                break;
            case 'user':
                $permissions = [
                    'can_manage_users' => false,
                    'can_manage_content' => false,
                    'can_view_reports' => false
                ];
                break;
        }

        return $permissions;
    }
}