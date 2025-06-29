<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Farmer\UserFarmer;

class JwtMiddleware 
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 401);
            }

            // ถอดรหัส token
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $userType = $payload->get('user_type', 'employee');

            // ตรวจสอบผู้ใช้ตามประเภท
            if ($userType === 'farmer') {
                $user = UserFarmer::find($userId);
                if (!$user || $user->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Farmer account not found or inactive'
                    ], 401);
                }

                    // เลือก supplier id ที่มีค่าจริง
$supplierId = $user->ma_kh_ca_nhan ?: $user->ma_kh_doanh_nghiep;
                
               $request->headers->set('X-User-Type', 'farmer');
    $request->headers->set('X-User-ID', $userId);
    $request->headers->set('X-Supplier-Number', $supplierId);
                
            } else {
                $user = User::find($userId);
                if (!$user || $user->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee account not found or inactive'
                    ], 401);
                }
                
                // เพิ่ม headers สำหรับ employee
                $request->headers->set('X-User-Type', 'employee');
                $request->headers->set('X-User-ID', $user->id);
                $request->headers->set('X-User-Position', $user->position);
                $request->headers->set('X-User-Station', $user->station);
                $request->headers->set('X-User-Ma-Nhan-Vien', $user->ma_nhan_vien);
            }

            return $next($request);

        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired'
            ], 401);
            
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid'
            ], 401);
            
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
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