<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Farmer\UserFarmer;

class JwtMiddleware 
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // ดึง token จาก Authorization header
            $token = $request->bearerToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 401);
            }

            // ตรวจสอบและถอดรหัส token
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            
            // ดึงข้อมูล user type จาก header
            $userType = $request->header('X-User-Type', 'employee');
            
            // ตรวจสอบผู้ใช้ตามประเภท
            if ($userType === 'farmer') {
                $user = UserFarmer::find($userId);
                if (!$user || $user->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Farmer account not found or inactive'
                    ], 401);
                }
                
                // เพิ่ม supplier number ใน header สำหรับใช้ใน controller
                $request->headers->set('X-User-Type', 'farmer');
                $request->headers->set('X-Supplier-Number', $user->supplier_number ?? $user->ma_kh_ca_nhan);
                
            } else {
                $user = User::find($userId);
                if (!$user || $user->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee account not found or inactive'
                    ], 401);
                }
                
                $request->headers->set('X-User-Type', 'employee');
            }
            
            // เพิ่มข้อมูลผู้ใช้ใน request สำหรับใช้ใน controller
            $request->merge(['authenticated_user' => $user]);
            $request->headers->set('X-User-ID', $userId);
            
            return $next($request);
            
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired'
            ], 401);
            
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid'
            ], 401);
            
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token error: ' . $e->getMessage()
            ], 401);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed'
            ], 500);
        }
    }
}