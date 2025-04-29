<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\DocumentDeliveryController;
use App\Http\Controllers\InvestmentProjectController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\Print\PrintGiaoNhanHSController;
use App\Http\Controllers\QuanlyHS\BienBanNghiemThuController;
use App\Http\Controllers\QuanlyHS\PhieuGiaoNhanHomGiongController;
use App\Http\Controllers\QuanlyHS\PaymentRequestController;


Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);


Route::group(['middleware' => ['auth:api']], function () {




    

    Route::get('/positions', [UserController::class, 'getPositions']);
    Route::get('/stations', [UserController::class, 'getStations']);
    Route::get('/roles', [UserController::class, 'getRoles']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/user/add', [UserController::class, 'addnewuser']);
    Route::put('/user/update/{id}', [UserController::class, 'updateuser']);
    Route::get('/user/edit/{id}', [UserController::class, 'edituser']);
    Route::delete('/user/delete/{id}', [UserController::class, 'deleteuser']);
    Route::get('/user/permissions', [UserController::class, 'getUserPermissions']);
    Route::get('/user/components', [UserController::class, 'getUserComponents']);

});


Route::post('/role/permissions', [RolePermissionController::class, 'store']);
Route::get('/roles', [RoleController::class, 'index']);
Route::get('/permissions', [PermissionController::class, 'index']);
Route::get('/components', [ComponentController::class, 'index']);
Route::get('/role/{role}/permissions', [RolePermissionController::class, 'getPermissions']);
Route::get('/role/{role}/components', [RolePermissionController::class, 'getComponents']);

//PAGE TAO BIEN BAN GIAO NHAN HO SO
Route::group(['middleware' => ['auth:api']], function () {
Route::get('/bienban-nghiemthu-search', [DocumentDeliveryController::class, 'searchBienBanNgheThu']);
Route::get('/investment-projects', function () {
    return DB::table('tb_vudautu')
        ->select('id', 'Ma_Vudautu', 'Ten_Vudautu')
        ->get();
});

Route::get('/document-deliveries/{id}/info', [DocumentDeliveryController::class, 'getDocumentInfo']);
Route::get('/document-types', [DocumentTypeController::class, 'index']);
// ลบ ได้หลายรายการ ในหน้ารายการเอกสาร
Route::delete('/document-deliveries', [DocumentDeliveryController::class, 'bulkDelete']);
Route::get('/document-deliveries', [DocumentDeliveryController::class, 'index']);
Route::post('/document-deliveries', [DocumentDeliveryController::class, 'store']);
Route::patch('/document-deliveries/{id}/status', [DocumentDeliveryController::class, 'updateStatus']);
Route::get('/bienban/search', [DocumentDeliveryController::class, 'searchBienBan']);
Route::post('/document-mappings', [DocumentDeliveryController::class, 'addMapping']);
Route::get('/document-mappings/{documentCode}', [DocumentDeliveryController::class, 'getMappings']);
Route::delete('/document-mappings/{id}', [DocumentDeliveryController::class, 'deleteMapping']);
Route::delete('document-deliveries/{id}', [DocumentDeliveryController::class, 'destroy']);
Route::put('/document-deliveries/{id}', [DocumentDeliveryController::class, 'update']);
Route::get('/bienban-homgiong-search', [DocumentDeliveryController::class, 'searchBienBanHomGiong']);
Route::post('/document-mappings-homgiong', [DocumentDeliveryController::class, 'addMappingHomGiong']);
Route::get('/document-mappings-homgiong/{documentCode}', [DocumentDeliveryController::class, 'getMappingsHomGiong']);
Route::delete('/document-mappings-homgiong/{id}', [DocumentDeliveryController::class, 'deleteMappingHomGiong']);
Route::get('/print/giaonhan-hoso/{document_code}', [PrintGiaoNhanHSController::class, 'print']);

Route::get('/document-deliveries/{id}', [DocumentDeliveryController::class, 'show']);

Route::get('/document-deliveries/{id}/check-access', [DocumentDeliveryController::class, 'checkAccess']);


  // เพิ่ม route สำหรับดึงข้อมูลผู้ใช้งาน
  Route::get('/user-info', function(Request $request) {
    return $request->user();
});


Route::get('/user-info', [UserController::class, 'getUserInfo']);

// เพิ่ม route สำหรับดึงข้อมูลบทบาทตามตำแหน่ง
Route::get('/get-role-by-position', function(Request $request) {
    $position = $request->query('position');
    
    $role = DB::table('listposition')
        ->where('position', $position)
        ->value('id_position');
        
    return response()->json(['role' => $role]);
});

//Load ข้อมูลตาลาง tb_bien_ban_nghiemthu_dv
Route::get('/bien-ban-nghiem-thu', [BienBanNghiemThuController::class, 'index']);
//Load details ข้อมูลตาลาง tb_bien_ban_nghiemthu_dv
Route::get('/bienban-nghiemthu/{id}', [BienBanNghiemThuController::class, 'show']);
// เพิ่ม route สำหรับตรวจสอบสิทธิ์การเข้าถึงเอกสาร bien-ban-nghiemthu
Route::get('/bien-ban-nghiemthu/{id}/check-access', [BienBanNghiemThuController::class, 'checkAccess']);

// Import data routes for BienBanNghiemThu
Route::post('/import-bienban-nghiemthu', [BienBanNghiemThuController::class, 'importData']);
Route::get('/import-progress/{importId}', [BienBanNghiemThuController::class, 'importProgress']);

// Routes for Phieu Giao Nhan Hom Giong
Route::get('/phieu-giao-nhan-hom-giong', [PhieuGiaoNhanHomGiongController::class, 'index']);
Route::post('/import-phieu-giao-nhan', [PhieuGiaoNhanHomGiongController::class, 'importData']);
Route::get('/import-homgiong-progress/{importId}', [PhieuGiaoNhanHomGiongController::class, 'importProgress']);
Route::get('/bienban-nghiemthu-homgiong/{id}', [PhieuGiaoNhanHomGiongController::class, 'show']);
Route::get('/bienban-nghiemthu-homgiong/{id}/check-access', [PhieuGiaoNhanHomGiongController::class, 'checkAccess']);

// ตรวจสอบรายการซ้ำซ้อน
Route::post('/check-payment-request-duplicates', [PaymentRequestController::class, 'checkDuplicates']);

// สร้างเอกสารขอเบิกเงิน
Route::post('/create-payment-request', [PaymentRequestController::class, 'createPaymentRequest']);

// ดึงรายการเอกสารขอเบิกเงินทั้งหมด
Route::get('/payment-requests', [PaymentRequestController::class, 'index']);

// ดึงข้อมูลโครงการลงทุน
Route::get('/investment-projects', [PaymentRequestController::class, 'getInvestmentProjects']);

// ดึงรายละเอียดเอกสารขอเบิกเงิน
// Add or modify this route:
    Route::get('/payment-requests/{id}', [PaymentRequestController::class, 'show']);

// อัพเดตสถานะเอกสารขอเบิกเงิน
Route::patch('/payment-requests/{id}/status', [PaymentRequestController::class, 'updateStatus']);
//ค้นหาเอกสารขอเบิกเงิน
Route::get('/bienban-nghiemthu-search-pttt', [DocumentDeliveryController::class, 'searchBienBanNgheThu_PTTT']);

//Add receive records to a payment request
Route::post('/payment-requests/{id}/add-receipts', [PaymentRequestController::class, 'addReceipts']);
//Import records for a payment request
Route::post('/payment-requests/{id}/import-data', [PaymentRequestController::class, 'importData']);
 // Update records for a payment request
 Route::post('/payment-requests/{id}/update-records', [PaymentRequestController::class, 'updateRecords']);
 
 // Delete records from a payment request
 Route::post('/payment-requests/{id}/delete-records', [PaymentRequestController::class, 'deleteRecords']);

});

