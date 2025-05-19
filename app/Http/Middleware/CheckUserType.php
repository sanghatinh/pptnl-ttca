<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserType
{
    /**
     * จัดการคำขอตามประเภทผู้ใช้
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $userType
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $userType = null)
    {
        // ดึงข้อมูลประเภทผู้ใช้จาก request
        $requestUserType = $request->attributes->get('user_type');
        
        // ถ้าไม่ได้ระบุประเภทผู้ใช้ที่ต้องการ หรือตรงกับประเภทผู้ใช้ที่กำลังใช้งาน
        if (!$userType || $requestUserType === $userType) {
            return $next($request);
        }
        
        // ถ้าไม่ตรงกัน ส่งข้อความแจ้งว่าไม่มีสิทธิ์
        return response()->json(['error' => 'Unauthorized access'], 403);
    }
}