<?php
// In the handle method, add support for farmer authentication
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
public function handle($request, Closure $next)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();
        
        // Check if token is for a farmer (which won't authenticate against the User model)
        if (!$user) {
            $payload = JWTAuth::parseToken()->getPayload();
            
            // If this token belongs to a farmer, handle differently
            if ($payload->get('user_type') === 'farmer') {
                $farmerId = $payload->get('sub');
                
                // Verify farmer exists and is active
                $farmer = DB::table('user_farmer')->where('id', $farmerId)->first();
                
                if (!$farmer || $farmer->status !== 'active') {
                    return response()->json(['error' => 'Tài khoản không tồn tại hoặc đã bị khóa'], 401);
                }
                
                // Add farmer to request for later use
                $request->attributes->add(['user_type' => 'farmer', 'user' => $farmer]);
                return $next($request);
            }
            
            return response()->json(['error' => 'Không tìm thấy người dùng'], 401);
        }
    } catch (TokenExpiredException $e) {
        return response()->json(['error' => 'Token đã hết hạn'], 401);
    } catch (TokenInvalidException $e) {
        return response()->json(['error' => 'Token không hợp lệ'], 401);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Token không được cung cấp'], 401);
    }

    // Add employee type to request
    $request->attributes->add(['user_type' => 'employee']);
    return $next($request);
}