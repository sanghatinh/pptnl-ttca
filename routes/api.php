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
use App\Http\Controllers\QuanlyTaichinh\PhieudenghithanhtoandvControllers;
use App\Http\Controllers\QuanlyTaichinh\PhieuthunodichvuController;
use App\Http\Controllers\QuanlyCongno\DeductibleServiceDebtController;
use App\Http\Controllers\QuanlyTaichinh\PhieutrinhthanhtoanHomgiongControllers;
use App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\QuanlyHS\ChiTietBienBanNghiemThuController;



Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);

// Get investment projects Table vụ đầu tư
Route::get('/investment-projects', [PaymentRequestController::class, 'getInvestmentProjects']);
// Add route for farmer login
Route::post('/farmer-login', [UserController::class, 'farmerLogin']);
Route::get('/farmer/permissions', [UserController::class, 'getFarmerPermissions']);
Route::get('/farmer/components', [UserController::class, 'getFarmerComponents']);

Route::group(['middleware' => ['auth:api']], function () {

    
 // Permission Management Routes
    Route::apiResource('permissions', App\Http\Controllers\PermissionController::class);
    // Role Management Routes
    Route::apiResource('roles', App\Http\Controllers\RoleController::class);

     // Dashboard Report Routes
    Route::get('/dashboard/chart-section', [App\Http\Controllers\Report\DashboardReportControllers::class, 'getReportChartSection']);
    Route::get('/dashboard/table-section', [App\Http\Controllers\Report\DashboardReportControllers::class, 'getReportTableSection']);
    Route::get('/dashboard/available-stations', [App\Http\Controllers\Report\DashboardReportControllers::class, 'getAvailableStations']);

    Route::get('/positions', [UserController::class, 'getPositions']);
    Route::get('/stations', [UserController::class, 'getStations']);
    Route::get('/roles', [UserController::class, 'getRoles']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/user/add', [UserController::class, 'addnewuser']);
    Route::post('/user/upload-image', [UserController::class, 'uploadUserImage']);
     Route::delete('/user/{id}/delete-image', [UserController::class, 'deleteUserImage']);
    Route::put('/user/update/{id}', [UserController::class, 'updateuser']);
    Route::get('/user/edit/{id}', [UserController::class, 'edituser']);
    Route::delete('/user/delete/{id}', [UserController::class, 'deleteuser']);
    Route::get('/user/permissions', [UserController::class, 'getUserPermissions']);
    Route::get('/user/components', [UserController::class, 'getUserComponents']);

      // My Profile Routes
    Route::get('/my-profile', [App\Http\Controllers\UserProfileController::class, 'getMyProfile']);
    Route::put('/my-profile', [App\Http\Controllers\UserProfileController::class, 'updateMyProfile']);
    Route::delete('/my-profile/image', [App\Http\Controllers\UserProfileController::class, 'deleteMyImage']);




});




Route::post('/role/permissions', [RolePermissionController::class, 'store']);
// Route::get('/roles', [RoleController::class, 'index']);
Route::get('/permissions', [PermissionController::class, 'index']);
Route::get('/components', [ComponentController::class, 'index']);
Route::get('/role/{role}/permissions', [RolePermissionController::class, 'getPermissions']);
Route::get('/role/{role}/components', [RolePermissionController::class, 'getComponents']);

//PAGE TAO BIEN BAN GIAO NHAN HO SO
Route::group(['middleware' => ['auth:api']], function () {
Route::get('/bienban-nghiemthu-search', [DocumentDeliveryController::class, 'searchBienBanNgheThu']);
Route::get('/investment-projects-original', function () {
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


  

// เพิ่ม route สำหรับดึงข้อมูลบทบาทตามตำแหน่ง
Route::get('/get-role-by-position', function(Request $request) {
    $position = $request->query('position');
    
    $role = DB::table('listposition')
        ->where('position', $position)
        ->value('id_position');
        
    return response()->json(['role' => $role]);
});




// Import data routes for BienBanNghiemThu
Route::post('/import-bienban-nghiemthu', [BienBanNghiemThuController::class, 'importData']);
Route::get('/import-progress/{importId}', [BienBanNghiemThuController::class, 'importProgress']);
Route::post('/import-chitiet-nghiemthu', [ChiTietBienBanNghiemThuController::class, 'importData']);
Route::get('/import-chitiet-nt-progress/{importId}', [ChiTietBienBanNghiemThuController::class, 'importProgress']);
// Routes for Phieu Giao Nhan Hom Giong

Route::post('/import-phieu-giao-nhan', [PhieuGiaoNhanHomGiongController::class, 'importData']);
Route::get('/import-homgiong-progress/{importId}', [PhieuGiaoNhanHomGiongController::class, 'importProgress']);
// Add these routes to the appropriate section (inside middleware group)
Route::post('/import-chitiet-hg', [App\Http\Controllers\QuanlyHS\ChiTietPhieugiaonhanHGController::class, 'importData']);
Route::get('/import-chitiet-hg-progress/{importId}', [App\Http\Controllers\QuanlyHS\ChiTietPhieugiaonhanHGController::class, 'importProgress']);


// ตรวจสอบรายการซ้ำซ้อน
Route::post('/check-payment-request-duplicates', [PaymentRequestController::class, 'checkDuplicates']);

// สร้างเอกสารขอเบิกเงิน
Route::post('/create-payment-request', [PaymentRequestController::class, 'createPaymentRequest']);

// ดึงรายการเอกสารขอเบิกเงินทั้งหมด
Route::get('/payment-requests', [PaymentRequestController::class, 'index']);


// ดึงรายละเอียดเอกสารขอเบิกเงิน
// Add or modify this route:
    Route::get('/payment-requests/{id}', [PaymentRequestController::class, 'show']);


//ค้นหาเอกสารขอเบิกเงิน
Route::get('/bienban-nghiemthu-search-pttt', [DocumentDeliveryController::class, 'searchBienBanNgheThu_PTTT']);

//Add receive records to a payment request
Route::post('/payment-requests/{id}/add-receipts', [PaymentRequestController::class, 'addReceipts']);
//Import records for a payment request
Route::post('/payment-requests/{id}/import-data', [PaymentRequestController::class, 'importData']);
 // Update records for a payment request
 Route::post('/payment-requests/{id}/update-records', [PaymentRequestController::class, 'updateRecords']);
 // Update basic info for payment request
 Route::put('/payment-requests/{id}/basic-info', [PaymentRequestController::class, 'updateBasicInfo']);
 Route::put('payment-requests/{id}/status', [PaymentRequestController::class, 'updateStatus']);
 // Add note saving route for payment requests
    Route::post('/payment-requests/{id}/notes', [PaymentRequestController::class, 'saveNote']);
    // Customer search routes for payment requests
Route::get('/search-individual-customers', [PaymentRequestController::class, 'searchIndividualCustomers']);
Route::get('/search-corporate-customers', [PaymentRequestController::class, 'searchCorporateCustomers']);
Route::get('/get-customer-by-name', [PaymentRequestController::class, 'getCustomerByName']);

 //Delete phiếu trình thanh toán dịch vụ
 Route::delete('/payment-requests/{id}', [PaymentRequestController::class, 'destroy']);
 
 // Delete records from a payment request
 Route::post('/payment-requests/{id}/delete-records', [PaymentRequestController::class, 'deleteRecords']);

//Phiếu đề nghi thanh toán trong phiếu trình thanh toán
Route::get('/payment-requests/{paymentCode}/history', [PaymentRequestController::class, 'getProcessingHistory']);
Route::get('/payment-requests/{id}/disbursements', [PhieudenghithanhtoandvControllers::class, 'getByPaymentRequest']);
Route::delete('/disbursements/bulk', [PhieudenghithanhtoandvControllers::class, 'bulkDelete']);
Route::put('/disbursements/bulk', [PhieudenghithanhtoandvControllers::class, 'bulkUpdate']);
Route::post('/disbursements/with-receipts', [PhieudenghithanhtoandvControllers::class, 'addWithReceipts']);
Route::post('/disbursements/export', [PhieudenghithanhtoandvControllers::class, 'export']);
Route::post('/disbursements/import', [PhieudenghithanhtoandvControllers::class, 'import']);
// Route::post('/print-preview-phieu-dntt', [App\Http\Controllers\Print\PrintPhieuDNTTControllers::class, 'getPrintPreview']);

// เพิ่มเส้นทาง API สำหรับอัปเดตข้อมูลทางการเงินของพิศูเญเงี้ยม
Route::post('/disbursements/update-financial', [PhieudenghithanhtoandvControllers::class, 'updateFinancialData']);
//Phiếu thu nợ dịch vụ

Route::get('/phieu-thu-no-dich-vu/{id}', [App\Http\Controllers\QuanlyTaichinh\PhieuthunodichvuController::class, 'show']);
Route::get('/phieu-thu-no-dich-vu/{id}/check-access', [App\Http\Controllers\QuanlyTaichinh\PhieuthunodichvuController::class, 'checkAccess']);
// Import Phieu Thu No Dich Vu routes
Route::post('/import-phieu-thu-no-dich-vu', [PhieuthunodichvuController::class, 'import']);
Route::get('/import-thu-no-dich-vu-progress/{importId}', [PhieuthunodichvuController::class, 'importProgress']);



 // Routes for Phieu trinh thanh toan hom giong (new)
    Route::get('/payment-requests-homgiong', [PhieutrinhthanhtoanHomgiongControllers::class, 'index']);
    Route::post('/check-payment-request-homgiong-duplicates', [PhieutrinhthanhtoanHomgiongControllers::class, 'checkDuplicates']);
    Route::post('/create-payment-request-homgiong', [PhieutrinhthanhtoanHomgiongControllers::class, 'createPaymentRequest']);
    Route::get('/payment-requests-homgiong/{id}/details', [PhieutrinhthanhtoanHomgiongControllers::class, 'show']);



    Route::put('/payment-requests-homgiong/{id}/records', [PhieutrinhthanhtoanHomgiongControllers::class, 'updateRecords']);
    Route::delete('/payment-requests-homgiong/{id}/records', [PhieutrinhthanhtoanHomgiongControllers::class, 'deleteRecords']);
    Route::get('/bienban-nghiemthu-homgiong-search-pttt', [DocumentDeliveryController::class, 'searchBienBanHomGiong_PTTT']);
    Route::post('/payment-requests-homgiong/{id}/receipts', [PhieutrinhthanhtoanHomgiongControllers::class, 'addReceipts']);
    Route::post('/payment-requests-homgiong/{id}/import', [PhieutrinhthanhtoanHomgiongControllers::class, 'importData']);
    Route::put('/payment-requests-homgiong/{id}/basic-info', [PhieutrinhthanhtoanHomgiongControllers::class, 'updateBasicInfo']);
     // Routes for Phieu de nghi thanh toan hom giong
    Route::post('/disbursements-homgiong/with-receipts', [App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers::class, 'addWithReceipts']);
    Route::get('/payment-requests/{id}/disbursements-homgiong', [App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers::class, 'getByPaymentRequest']);
    // เพิ่ม route ใหม่สำหรับการปรับปรุงยอดเงินตาม harvest costs
Route::put('/disbursements-homgiong/update-hold-amount', [App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers::class, 'updateHoldAmount']);
Route::delete('/disbursements-homgiong/bulk', [App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers::class, 'bulkDelete']);
Route::put('/disbursements-homgiong/bulk', [App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers::class, 'bulkUpdate']);
Route::post('/disbursements-homgiong/import', [App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers::class, 'import']);
Route::get('/disbursements-homgiong/import-template', [App\Http\Controllers\QuanlyTaichinh\PhieuDNTTHomgiongControllers::class, 'downloadTemplate']);
// Routes สำหรับ PhieuDNTTHomgiongControllers - new methods
Route::put('/payment-requests-homgiong/{id}/status', [PhieuDNTTHomgiongControllers::class, 'updateStatusHomgiong']);
Route::get('/payment-requests-homgiong/{paymentCode}/history', [PhieuDNTTHomgiongControllers::class, 'getProcessingHistoryHomgiong']);
Route::post('/payment-requests-homgiong/{id}/notes', [PhieuDNTTHomgiongControllers::class, 'saveNoteHomgiong']);
Route::put('/payment-requests-homgiong/{id}/basic-info', [PhieuDNTTHomgiongControllers::class, 'updateBasicInfoHomgiong']);
Route::delete('/payment-requests-homgiong/{id}', [PhieuDNTTHomgiongControllers::class, 'destroyHomgiong']);





//Công nợ dịch vụ khấu trừ
Route::get('/import-congno-dichvu-khautru-progress/{importId}', [DeductibleServiceDebtController::class, 'checkImportProgress']);
Route::post('/import-congno-dichvu-khautru', [DeductibleServiceDebtController::class, 'startImport']);

// Farmer Users Management
    Route::get('/farmer-users', [App\Http\Controllers\Farmer\UserFarmerController::class, 'index']);
    Route::get('/farmer-users/filter-options', [App\Http\Controllers\Farmer\UserFarmerController::class, 'getFilterOptions']);
    Route::get('/farmer-users/{id}', [App\Http\Controllers\Farmer\UserFarmerController::class, 'show']);
    Route::post('/farmer-users', [App\Http\Controllers\Farmer\UserFarmerController::class, 'store']);
    Route::put('/farmer-users/{id}', [App\Http\Controllers\Farmer\UserFarmerController::class, 'update']);
    Route::delete('/farmer-users/{id}', [App\Http\Controllers\Farmer\UserFarmerController::class, 'destroy']);
    // Farmer Users Import Routes
Route::post('/import-farmer-users', [App\Http\Controllers\Farmer\UserFarmerController::class, 'importData']);
Route::get('/import-farmer-users-progress/{importId}', [App\Http\Controllers\Farmer\UserFarmerController::class, 'importProgress']);
Route::get('/farmer-users-template', [App\Http\Controllers\Farmer\UserFarmerController::class, 'downloadTemplate']);
  // Sync selected farmer users to userfarmer_role
    Route::post('/farmer-users/sync-roles', [App\Http\Controllers\Farmer\UserFarmerController::class, 'syncToUserFarmerRoles']);
        Route::post('/farmer-users/delete-multiple', [App\Http\Controllers\Farmer\UserFarmerController::class, 'deleteMultiple']);
//Dropdown options for farmer users
Route::get('/employees/by-station', [UserController::class, 'getEmployeesByStation']);

    // เพิ่ม routes สำหรับรูปภาพ
    Route::post('/farmer-users/upload-image', [App\Http\Controllers\Farmer\UserFarmerController::class, 'uploadFarmerImage']);
    Route::delete('/farmer-users/{id}/delete-image', [App\Http\Controllers\Farmer\UserFarmerController::class, 'deleteFarmerImage']);
// Calender Routes

   Route::get('/payment-schedule/calendar-events', [App\Http\Controllers\Calender\LichThanhToanControllers::class, 'getCalendarEvents']);
    Route::get('/payment-schedule/service-tracking', [App\Http\Controllers\Calender\LichThanhToanControllers::class, 'getServicePaymentTracking']);
    Route::get('/payment-schedule/seed-tracking', [App\Http\Controllers\Calender\LichThanhToanControllers::class, 'getSeedPaymentTracking']);
  // Lịch Giao Nhận Hồ Sơ Routes
    Route::get('/document-schedule/calendar-events', [App\Http\Controllers\Calender\LichgiaonhanHosoControllers::class, 'getCalendarEvents']);
    Route::get('/document-schedule/transactions', [App\Http\Controllers\Calender\LichgiaonhanHosoControllers::class, 'getDocumentTransactions']);

});

// เพิ่มนอก middleware เพื่อให้เข้าถึง print page ได้
Route::get('/print-phieu-dntt', [App\Http\Controllers\Print\PrintPhieuDNTTControllers::class, 'printGet']);
Route::post('/print-phieu-dntt', [App\Http\Controllers\Print\PrintPhieuDNTTControllers::class, 'print']);
Route::post('/print-preview-phieu-dntt', [App\Http\Controllers\Print\PrintPhieuDNTTControllers::class, 'getPrintPreview']);
Route::get('/print-phieu-dntt-hg', [App\Http\Controllers\Print\PrintGiaoNhanHSController::class, 'printPDNTTHG']);
Route::post('/print-phieu-dntt-hg', [App\Http\Controllers\Print\PrintGiaoNhanHSController::class, 'printPDNTTHG']);
Route::post('/print-preview-phieu-dntt-hg', [App\Http\Controllers\Print\PrintGiaoNhanHSController::class, 'getPrintPreviewPDNTTHG']);
Route::post('/print-report-dntt-dv', [App\Http\Controllers\Print\PrintGiaoNhanHSController::class, 'printDocumentReportDNTTDV']);
Route::post('/print-report-dntt-hg', [App\Http\Controllers\Print\PrintGiaoNhanHSController::class, 'printDocumentReportDNTTHG']);
// เพิ่ม Route สำหรับ Report PDF Bien Ban Nghiem Thu DV
Route::match(['GET', 'POST'], '/generate-report-bienban-ntdv', [BienBanNghiemThuController::class, 'generateReportTableBienBanNTDV']);
// เพิ่ม Route สำหรับ Report PDF Phieu Giao Nhan Hom Giong  
Route::match(['GET', 'POST'], '/generate-report-phieugiaonhan-hg', [PhieuGiaoNhanHomGiongController::class, 'generateReportTablePhieugiaonhanHG']);
// เพิ่ม Route สำหรับ Report PDF Phieu De Nghi Thanh Toan DV
Route::match(['GET', 'POST'], '/generate-report-phieu-dntt-dv', [PhieudenghithanhtoandvControllers::class, 'generateReportTableDNTTDV']);

// เพิ่ม Route สำหรับ Report PDF Phieu De Nghi Thanh Toan Hom Giong
Route::match(['GET', 'POST'], '/generate-report-phieu-dntt-hg', [PhieuDNTTHomgiongControllers::class, 'generateReportTableDNTTHG']);
// เพิ่ม Route สำหรับ Report PDF Công nợ dịch vụ phải thu
Route::match(['GET', 'POST'], '/generate-report-congno-phaithu', [DeductibleServiceDebtController::class, 'generateReportTableCongnoPhaithu']);
// เพิ่ม Route สำหรับ Report PDF Phieu Thu No Dich Vu
Route::match(['GET', 'POST'], '/generate-report-table-phieuthuno', [PhieuthunodichvuController::class, 'generateReportTablePhieuthuno']);

//Báo cáo phiếu trình thanh toán
Route::get('/print-report-phieu-trinh-tt', [App\Http\Controllers\Print\PrintPhieuTrinhThanhtoanControllers::class, 'generateReportPhieuTrinhTT']);
Route::get('/print-report-phieu-trinh-tt-homgiong', [App\Http\Controllers\Print\PrintPhieuTrinhThanhtoanControllers::class, 'generateReportPhieuTrinhTTHomgiong']);



// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Middleware for JWT authentication เพื่อป้องกันการเข้าถึง API ที่ต้องการการยืนยันตัวตน ให้ใช้ได้ ทั้ง ผู้ดูแลระบบและเกษตรกร
Route::middleware([\App\Http\Middleware\JwtMiddleware::class])->group(function () {
Route::get('/congno-dichvu-khautru', [DeductibleServiceDebtController::class, 'index']);
Route::get('/congno-dichvu-khautru/{invoicenumber}', [DeductibleServiceDebtController::class, 'showDetails']);
Route::get('/congno-dichvu-khautru/{invoicenumber}/check-access', [DeductibleServiceDebtController::class, 'checkAccess']);
//Phieu thu nợ dịch vụ
Route::get('/phieu-thu-no-dich-vu', [App\Http\Controllers\QuanlyTaichinh\PhieuthunodichvuController::class, 'index']);
//Load ข้อมูลตาลาง tb_bien_ban_nghiemthu_dv
Route::get('/bien-ban-nghiem-thu', [BienBanNghiemThuController::class, 'index']);
//Load details ข้อมูลตาลาง tb_bien_ban_nghiemthu_dv
Route::get('/bienban-nghiemthu/{id}', [BienBanNghiemThuController::class, 'show']);
// เพิ่ม route สำหรับตรวจสอบสิทธิ์การเข้าถึงเอกสาร bien-ban-nghiemthu
Route::get('/bien-ban-nghiemthu/{id}/check-access', [BienBanNghiemThuController::class, 'checkAccess']);
//Timeline bieenban-nghiemthu
Route::get('/bienban-nghiemthu/{id}/history', [BienBanNghiemThuController::class, 'processingHistoryNghiemthuDV']);

//สำหรับดึงข้อมูลผู้ใช้งาน
Route::get('/user-info', [UserController::class, 'getUserInfo']);
//Phiếu giao nhận hom giống
Route::get('/phieu-giao-nhan-hom-giong', [PhieuGiaoNhanHomGiongController::class, 'index']);
Route::get('/bienban-nghiemthu-homgiong/{id}', [PhieuGiaoNhanHomGiongController::class, 'show']);
Route::get('/bienban-nghiemthu-homgiong/{id}/check-access', [PhieuGiaoNhanHomGiongController::class, 'checkAccess']);
Route::get('/bienban-nghiemthu-homgiong/{id}/history', [PhieuGiaoNhanHomGiongController::class, 'getHistory']);
Route::post('/users/get-by-ids', [UserController::class, 'getUsersByIds']);

 // Farmer Profile Management Routes
    Route::get('/farmer/my-profile', [App\Http\Controllers\Farmer\FarmerProfileController::class, 'getMyProfile']);
    Route::put('/farmer/update-profile', [App\Http\Controllers\Farmer\FarmerProfileController::class, 'updateProfile']);
    Route::post('/farmer/upload-image', [App\Http\Controllers\Farmer\FarmerProfileController::class, 'uploadImage']);
    Route::delete('/farmer/delete-image', [App\Http\Controllers\Farmer\FarmerProfileController::class, 'deleteImage']);
    // Banks API for farmer dropdown
    Route::get('/farmer/banks', [App\Http\Controllers\Farmer\FarmerProfileController::class, 'getBanks']);

    //Phiếu nghị đề thanh toán dịch vụ
Route::get('/tai-chinh/phieu-de-nghi-thanh-toan-dv', [PhieudenghithanhtoandvControllers::class, 'getAllPaymentRequests']);
Route::get('/payment-requests-dichvu/{id}', [PhieudenghithanhtoandvControllers::class, 'showDetailPayment']);
Route::get('/payment-requests-dichvu/{id}/bienban-nghiemthu', [PhieudenghithanhtoandvControllers::class, 'getbienbannghiemthudv']);
Route::get('/payment-requests-dichvu/{id}/chitiet-dichvu', 'App\Http\Controllers\QuanlyTaichinh\PhieudenghithanhtoandvControllers@getchitietbienbannghiemthudv');
Route::get('/payment-requests-dichvu/{id}/phieu-thu-no', [PhieudenghithanhtoandvControllers::class, 'getphieuthunodv']);
Route::get('/payment-requests-dichvu/{id}/processing-history', [PaymentRequestController::class, 'getDisbursementProcessingHistory']);
Route::put('/payment-requests-dichvu/{id}/update', [PhieudenghithanhtoandvControllers::class, 'updateDocument']);

    Route::get('/payment-requests-dichvu/{id}/check-access', [PhieudenghithanhtoandvControllers::class, 'checkAccess']);

//Phiếu đề nghị thanh toán hom giống
Route::get('/tai-chinh/phieu-de-nghi-thanh-toan-homgiong', [PhieuDNTTHomgiongControllers::class, 'getAllPaymentRequestsHomgiong']);
Route::get('/payment-requests-homgiong/{id}', [PhieuDNTTHomgiongControllers::class, 'showDetailPayment']);

  Route::get('/payment-requests-homgiong/{id}/phieu-giao-nhan', [PhieuDNTTHomgiongControllers::class, 'getphieugiaonhanhomgiong']);
Route::get('/payment-requests-homgiong/{id}/chitiet-giaonhan', [PhieuDNTTHomgiongControllers::class, 'getchitietgiaonhanhomgiong']);

    Route::get('/payment-requests-homgiong/{id}/processing-history', [PhieuDNTTHomgiongControllers::class, 'getDisbursementProcessingHistoryHomgiong']);
    Route::put('/payment-requests-homgiong/{id}/update', [PhieuDNTTHomgiongControllers::class, 'updateDocumentHomgiong']);
    Route::get('/payment-requests-homgiong/{id}/check-access', [PhieuDNTTHomgiongControllers::class, 'checkAccessHomgiong']);
// Farmer Dashboard Routes
    Route::get('/farmer/dashboard/financial-summary', [App\Http\Controllers\Report\DashboardFarmerReportControllers::class, 'getFinancialSummary']);
    Route::get('/farmer/dashboard/debt-vs-interest', [App\Http\Controllers\Report\DashboardFarmerReportControllers::class, 'getDebtPaymentVsInterestData']);
    Route::get('/farmer/dashboard/payment-type-distribution', [App\Http\Controllers\Report\DashboardFarmerReportControllers::class, 'getDebtPaymentTypeDistribution']);
    Route::get('/farmer/dashboard/payment-requests', [App\Http\Controllers\Report\DashboardFarmerReportControllers::class, 'getPaymentRequests']);
    Route::get('/farmer/dashboard/work-items', [App\Http\Controllers\Report\DashboardFarmerReportControllers::class, 'getWorkItems']);
    

});


