<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log\AttendanceLogs;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;

class AttendanceLogsController extends Controller
{
    /**
     * แสดงรายการลงเวลา
     */
    public function index(Request $request)
    {
        $query = AttendanceLogs::query();

        // Global search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('date', 'like', "%{$searchTerm}%")
                  ->orWhere('note_morning', 'like', "%{$searchTerm}%")
                  ->orWhere('note_evening', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('full_name', 'like', "%{$searchTerm}%")
                               ->orWhere('username', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Date filter
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        // Full name filter
        if ($request->filled('full_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->full_name}%");
            });
        }

        // Station filter
        if ($request->filled('station') && is_array($request->station) && count($request->station) > 0) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->whereIn('station', $request->station);
            });
        }

        // Position filter
        if ($request->filled('position') && is_array($request->position) && count($request->position) > 0) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->whereIn('position', $request->position);
            });
        }

        // Status filter
        if ($request->filled('status') && is_array($request->status) && count($request->status) > 0) {
            $statusConditions = [];
            foreach ($request->status as $status) {
                switch ($status) {
                    case 'full_day':
                        $statusConditions[] = function ($q) {
                            $q->whereNotNull('checkin_morning')->whereNotNull('checkin_evening');
                        };
                        break;
                    case 'morning_only':
                        $statusConditions[] = function ($q) {
                            $q->whereNotNull('checkin_morning')->whereNull('checkin_evening');
                        };
                        break;
                    case 'evening_only':
                        $statusConditions[] = function ($q) {
                            $q->whereNull('checkin_morning')->whereNotNull('checkin_evening');
                        };
                        break;
                    case 'no_checkin':
                        $statusConditions[] = function ($q) {
                            $q->whereNull('checkin_morning')->whereNull('checkin_evening');
                        };
                        break;
                }
            }

            if (!empty($statusConditions)) {
                $query->where(function ($q) use ($statusConditions) {
                    foreach ($statusConditions as $condition) {
                        $q->orWhere($condition);
                    }
                });
            }
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter by user position
        $user = auth()->user();
        $position = $user->userPosition ?? $user->position ?? null;
        \Log::info('AttendanceLogsController@index - Auth user', [
            'id' => $user->id ?? null,
            'position' => $position,
        ]);
        if ($user) {
            switch ($position) {
                case 'department_head':
                case 'office_workers':
                    break;
                case 'Station_Chief':
                    if ($user->station) {
                        $query->whereHas('user', function ($q) use ($user) {
                            $q->where('station', $user->station);
                        });
                    }
                    break;
                case 'Farm_worker':
                    case 'Mechanical_worker':
                    $query->where('users_id', $user->id);
                    break;
                default:
                    $query->whereRaw('1=0');
            }
        }

        // Determine per_page
        $perPage = $request->get('per_page', 20);
        if ($perPage == -1) {
            $perPage = 99999; // Get all records for export
        }

        $logs = $query->with('user')->orderBy('date', 'desc')->paginate($perPage);

        // เพิ่มการคำนวณ Status ตามเงื่อนไขที่ระบุ
        $logs->getCollection()->transform(function ($log) {
            if ($log->checkin_morning && $log->checkin_evening) {
                $log->status = "full_day";
            } elseif ($log->checkin_morning) {
                $log->status = "morning_only";
            } elseif ($log->checkin_evening) {
                $log->status = "evening_only";
            } else {
                $log->status = "no_checkin";
            }
            return $log;
        });

        return response()->json(['success' => true, 'data' => $logs]);
    }

    /**
     * สร้างลงเวลา (เช้า/เย็น)
     */
public function store(Request $request)
{
    // Log ข้อมูล request ทั้งหมด
    \Log::info('AttendanceLogsController@store - Incoming request', [
        'user_id' => $request->input('users_id'),
        'date' => $request->input('date'),
        'lat_morning' => $request->input('lat_morning'),
        'lng_morning' => $request->input('lng_morning'),
        'lat_evening' => $request->input('lat_evening'),
        'lng_evening' => $request->input('lng_evening'),
        'has_photo_morning' => $request->hasFile('photo_morning'),
        'has_photo_evening' => $request->hasFile('photo_evening'),
        'all_except_files' => $request->except(['photo_morning', 'photo_evening']),
        'files' => $request->allFiles(),
    ]);

    $validator = Validator::make($request->all(), [
        'users_id' => 'required|integer|exists:users,id',
        'date' => 'required|date',
        'lat_morning' => 'nullable|numeric|between:-90,90',
        'lng_morning' => 'nullable|numeric|between:-180,180',
        'note_morning' => 'nullable|string',
        'lat_evening' => 'nullable|numeric|between:-90,90',
        'lng_evening' => 'nullable|numeric|between:-180,180',
        'note_evening' => 'nullable|string',
        'photo_morning' => 'nullable|image|max:5120', // 5MB max
        'photo_evening' => 'nullable|image|max:5120', // 5MB max
    ]);

    if ($validator->fails()) {
        \Log::error('AttendanceLogsController@store - Validation failed', [
            'errors' => $validator->errors()->all()
        ]);
        return response()->json([
            'success' => false, 
            'errors' => $validator->errors()
        ], 422);
    }

    // Get authenticated user
    $user = $request->user();
    if (!$user) {
        \Log::error('AttendanceLogsController@store - No authenticated user');
        return response()->json([
            'success' => false,
            'message' => 'Authentication required'
        ], 401);
    }

    $data = $request->only([
        'users_id', 'date', 'note_morning', 'note_evening',
        'lat_morning', 'lng_morning', 'lat_evening', 'lng_evening'
    ]);

    // ตรวจสอบว่า date ต้องเป็นวันนี้เท่านั้น
    $today = now()->toDateString();
    if ($data['date'] !== $today) {
        return response()->json([
            'success' => false,
            'message' => 'Bạn chỉ có thể điểm danh cho ngày hôm nay'
        ], 422);
    }

    // Ensure users_id matches authenticated user (for security)
    if ($data['users_id'] != $user->id) {
        \Log::warning('AttendanceLogsController@store - User ID mismatch', [
            'authenticated_user' => $user->id,
            'submitted_user_id' => $data['users_id']
        ]);
        $data['users_id'] = $user->id;
    }

    // ตรวจสอบว่ามี log วันนี้ของ user นี้แล้วหรือยัง
    $existingLog = AttendanceLogs::where('users_id', $user->id)
        ->where('date', $data['date'])
        ->first();
    
    if ($existingLog) {
        return response()->json([
            'success' => false,
            'message' => 'Bạn đã điểm danh hôm nay'
        ], 409);
    }

    // ตรวจสอบช่วงเวลาที่อนุญาตให้ checkin
    $currentTime = now()->setTimezone('Asia/Bangkok');
    $currentHour = $currentTime->hour;
    $currentMinute = $currentTime->minute;
    $currentTimeInMinutes = ($currentHour * 60) + $currentMinute;

    // ช่วงเวลาเช้า: 6:00 - 9:00 (360 - 540 นาที)
    $morningStart = 6 * 60; // 6:00 = 360 นาที
    $morningEnd = 9 * 60;   // 9:00 = 540 นาที

    // ช่วงเวลาเย็น: 16:00 - 18:00 (960 - 1080 นาที)
    $eveningStart = 16 * 60; // 16:00 = 960 นาที
    $eveningEnd = 18 * 60;   // 18:00 = 1080 นาที

    // ตรวจสอบว่าสามารถ checkin ได้หรือไม่
    $canCheckinMorning = ($currentTimeInMinutes >= $morningStart && $currentTimeInMinutes <= $morningEnd);
    $canCheckinEvening = ($currentTimeInMinutes >= $eveningStart && $currentTimeInMinutes <= $eveningEnd);

    // ถ้ามีการอัปโหลดรูปเช้า แต่ไม่อยู่ในช่วงเวลาเช้า
    if ($request->hasFile('photo_morning') && !$canCheckinMorning) {
        return response()->json([
            'success' => false,
            'message' => 'Chỉ có thể điểm danh sáng từ 6:00 đến 9:00'
        ], 422);
    }

    // ถ้ามีการอัปโหลดรูปเย็น แต่ไม่อยู่ในช่วงเวลาเย็น
    if ($request->hasFile('photo_evening') && !$canCheckinEvening) {
        return response()->json([
            'success' => false,
            'message' => 'Chỉ có thể điểm danh chiều từ 16:00 đến 18:00'
        ], 422);
    }

    // ถ้าไม่ได้อยู่ในช่วงเวลาที่อนุญาต
    if (!$canCheckinMorning && !$canCheckinEvening) {
        return response()->json([
            'success' => false,
            'message' => 'Chỉ có thể điểm danh trong khung giờ: 6:00-9:00 (sáng) hoặc 16:00-18:00 (chiều)'
        ], 422);
    }

    $cloudinaryService = new CloudinaryService();

    // เช็คอินเช้า
    if ($request->hasFile('photo_morning')) {
        $result = $cloudinaryService->uploadImageOptimized(
            $request->file('photo_morning'),
            [
                'folder' => 'attendance_logs',
                'quality' => 'auto:good',
                'format' => 'jpg',
                'transformation' => [
                    'width' => 1024,
                    'height' => 1024,
                    'crop' => 'limit',
                    'quality' => 'auto:good',
                    'fetch_format' => 'jpg'
                ]
            ]
        );
        // ถ้าไฟล์ใหญ่เกิน 500KB ให้บีบอัดใหม่
        if ($result['success'] && $result['bytes'] > 512000) {
            \Log::info('Recompressing photo_morning with lower quality', ['bytes' => $result['bytes']]);
            $result = $cloudinaryService->uploadImageOptimized(
                $request->file('photo_morning'),
                [
                    'folder' => 'attendance_logs',
                    'quality' => 'auto:low',
                    'format' => 'jpg',
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                        'quality' => 'auto:low',
                        'fetch_format' => 'jpg'
                    ]
                ]
            );
        }
        \Log::info('AttendanceLogsController@store - photo_morning upload result', $result);
        if ($result['success']) {
            $data['photo_morning'] = $result['public_id'];
            $data['checkin_morning'] = now();
        } else {
            \Log::error('AttendanceLogsController@store - photo_morning upload failed', [
                'message' => $result['message'] ?? 'Upload failed'
            ]);
            return response()->json([
                'success' => false, 
                'message' => $result['message'] ?? 'Upload failed'
            ], 500);
        }
    }

    // เช็คอินเย็น
    if ($request->hasFile('photo_evening')) {
        $result = $cloudinaryService->uploadImageOptimized(
            $request->file('photo_evening'),
            [
                'folder' => 'attendance_logs',
                'quality' => 'auto:good',
                'format' => 'jpg',
                'transformation' => [
                    'width' => 1200,
                    'height' => 1200,
                    'crop' => 'limit',
                    'quality' => 'auto:good',
                    'fetch_format' => 'jpg'
                ]
            ]
        );
        if ($result['success'] && $result['bytes'] > 512000) {
            \Log::info('Recompressing photo_evening with lower quality', ['bytes' => $result['bytes']]);
            $result = $cloudinaryService->uploadImageOptimized(
                $request->file('photo_evening'),
                [
                    'folder' => 'attendance_logs',
                    'quality' => 'auto:low',
                    'format' => 'jpg',
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                        'quality' => 'auto:low',
                        'fetch_format' => 'jpg'
                    ]
                ]
            );
        }
        \Log::info('AttendanceLogsController@store - photo_evening upload result', $result);
        if ($result['success']) {
            $data['photo_evening'] = $result['public_id'];
            $data['checkin_evening'] = now();
        } else {
            \Log::error('AttendanceLogsController@store - photo_evening upload failed', [
                'message' => $result['message'] ?? 'Upload failed'
            ]);
            return response()->json([
                'success' => false, 
                'message' => $result['message'] ?? 'Upload failed'
            ], 500);
        }
    }

    try {
        $attendance = AttendanceLogs::create($data);
        \Log::info('AttendanceLogsController@store - Attendance created', [
            'attendance_id' => $attendance->id,
            'data' => $attendance->toArray()
        ]);
        return response()->json([
            'success' => true, 
            'data' => $attendance,
            'message' => 'Attendance logged successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error('AttendanceLogsController@store - Exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false, 
            'message' => 'Failed to save attendance: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * ดูรายละเอียดลงเวลา
     */
    public function show($id)
    {
        $attendance = AttendanceLogs::with('user')->find($id);
        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $attendance]);
    }

    /**
     * แก้ไขลงเวลา
     */
   public function update(Request $request, $id)
{
    $attendance = AttendanceLogs::find($id);
    if (!$attendance) {
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    $validator = Validator::make($request->all(), array_merge(
        AttendanceLogs::rules($id),
        [
            'date' => 'required|date',
            'users_id' => 'required|integer|exists:users,id',
            'photo_morning' => 'nullable|image|max:5120', // เพิ่ม validation รูปเช้า
            'photo_evening' => 'nullable|image|max:5120', // เพิ่ม validation รูปเย็น
        ]
    ));
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $data = $request->only([
        'users_id',
        'date','note_morning', 'note_evening',
        'lat_morning', 'lng_morning', 'lat_evening', 'lng_evening'
    ]);

    // ตรวจสอบช่วงเวลาที่อนุญาตให้ checkin
    $currentTime = now()->setTimezone('Asia/Bangkok');
    $currentHour = $currentTime->hour;
    $currentMinute = $currentTime->minute;
    $currentTimeInMinutes = ($currentHour * 60) + $currentMinute;

    // ช่วงเวลาเช้า: 6:00 - 9:00 (360 - 540 นาที)
    $morningStart = 6 * 60; // 6:00 = 360 นาที
    $morningEnd = 9 * 60;   // 9:00 = 540 นาที

    // ช่วงเวลาเย็น: 16:00 - 18:00 (960 - 1080 นาที)
    $eveningStart = 16 * 60; // 16:00 = 960 นาที
    $eveningEnd = 18 * 60;   // 18:00 = 1080 นาที

    // ตรวจสอบว่าสามารถ checkin ได้หรือไม่
    $canCheckinMorning = ($currentTimeInMinutes >= $morningStart && $currentTimeInMinutes <= $morningEnd);
    $canCheckinEvening = ($currentTimeInMinutes >= $eveningStart && $currentTimeInMinutes <= $eveningEnd);

    // ถ้ามีการอัปโหลดรูปเช้า แต่ไม่อยู่ในช่วงเวลาเช้า
    if ($request->hasFile('photo_morning') && !$canCheckinMorning) {
        return response()->json([
            'success' => false,
            'message' => 'Chỉ có thể cập nhật điểm danh sáng từ 6:00 đến 9:00'
        ], 422);
    }

    // ถ้ามีการอัปโหลดรูปเย็น แต่ไม่อยู่ในช่วงเวลาเย็น
    if ($request->hasFile('photo_evening') && !$canCheckinEvening) {
        return response()->json([
            'success' => false,
            'message' => 'Chỉ có thể cập nhật điểm danh chiều từ 16:00 đến 18:00'
        ], 422);
    }

    // ถ้าไม่ได้อยู่ในช่วงเวลาที่อนุญาต และมีการอัปโหลดรูป
    if (($request->hasFile('photo_morning') || $request->hasFile('photo_evening')) && 
        !$canCheckinMorning && !$canCheckinEvening) {
        return response()->json([
            'success' => false,
            'message' => 'Chỉ có thể cập nhật điểm danh trong khung giờ: 6:00-9:00 (sáng) hoặc 16:00-18:00 (chiều)'
        ], 422);
    }

    $cloudinaryService = new CloudinaryService();

    // อัปเดตรูปเช้า
    if ($request->hasFile('photo_morning')) {
        $result = $cloudinaryService->uploadImageOptimized(
            $request->file('photo_morning'),
            [
                'folder' => 'attendance_logs',
                'quality' => 'auto:good',
                'format' => 'jpg',
                'transformation' => [
                    'width' => 1024,
                    'height' => 1024,
                    'crop' => 'limit',
                    'quality' => 'auto:good',
                    'fetch_format' => 'jpg'
                ]
            ]
        );
        // ถ้าไฟล์ใหญ่เกิน 500KB ให้บีบอัดใหม่
        if ($result['success'] && $result['bytes'] > 512000) {
            \Log::info('Recompressing photo_morning with lower quality', ['bytes' => $result['bytes']]);
            $result = $cloudinaryService->uploadImageOptimized(
                $request->file('photo_morning'),
                [
                    'folder' => 'attendance_logs',
                    'quality' => 'auto:low',
                    'format' => 'jpg',
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                        'quality' => 'auto:low',
                        'fetch_format' => 'jpg'
                    ]
                ]
            );
        }
        if ($result['success']) {
            $data['photo_morning'] = $result['public_id'];
            $data['checkin_morning'] = now();
        } else {
            return response()->json([
                'success' => false, 
                'message' => $result['message'] ?? 'Upload failed'
            ], 500);
        }
    }

    // อัปเดตรูปเย็น
    if ($request->hasFile('photo_evening')) {
        $result = $cloudinaryService->uploadImageOptimized(
            $request->file('photo_evening'),
            [
                'folder' => 'attendance_logs',
                'quality' => 'auto:good',
                'format' => 'jpg',
                'transformation' => [
                    'width' => 1200,
                    'height' => 1200,
                    'crop' => 'limit',
                    'quality' => 'auto:good',
                    'fetch_format' => 'jpg'
                ]
            ]
        );
        if ($result['success'] && $result['bytes'] > 512000) {
            \Log::info('Recompressing photo_evening with lower quality', ['bytes' => $result['bytes']]);
            $result = $cloudinaryService->uploadImageOptimized(
                $request->file('photo_evening'),
                [
                    'folder' => 'attendance_logs',
                    'quality' => 'auto:low',
                    'format' => 'jpg',
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                        'quality' => 'auto:low',
                        'fetch_format' => 'jpg'
                    ]
                ]
            );
        }
        if ($result['success']) {
            $data['photo_evening'] = $result['public_id'];
            $data['checkin_evening'] = now();
        } else {
            return response()->json([
                'success' => false, 
                'message' => $result['message'] ?? 'Upload failed'
            ], 500);
        }
    }

    $attendance->update($data);

    return response()->json(['success' => true, 'data' => $attendance]);
}
    /**
     * ลบลงเวลา
     */
    public function destroy($id)
    {
        $attendance = AttendanceLogs::find($id);
        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        // ลบรูปจาก Cloudinary ถ้ามี
        $cloudinaryService = new CloudinaryService();
        if ($attendance->photo_morning) {
            $cloudinaryService->deleteImageEnhanced($attendance->photo_morning);
        }
        if ($attendance->photo_evening) {
            $cloudinaryService->deleteImageEnhanced($attendance->photo_evening);
        }

        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Deleted']);
    }


 public function exportExcel(Request $request)
{
    // \Log::info('AttendanceLogsController@exportExcel - START', [
    //     'params' => $request->all(),
    //     'method' => $request->method(),
    //     'url' => $request->fullUrl(),
    //     'ip' => $request->ip()
    // ]);

    try {
        // ตรวจสอบ JWT/Bearer token
        $user = auth()->user();
        $position = $user->userPosition ?? $user->position ?? null;
        
        // \Log::info('AttendanceLogsController@exportExcel - Auth check', [
        //     'user_id' => $user->id ?? null,
        //     'position' => $position,
        //     'has_user' => !!$user
        // ]);

        if (!$user) {
           
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // สร้าง query เหมือน index
        $query = AttendanceLogs::query();

        // \Log::info('AttendanceLogsController@exportExcel - Building query', [
        //     'initial_count' => $query->count()
        // ]);

        // Apply all filters like in index method
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('date', 'like', "%{$searchTerm}%")
                  ->orWhere('note_morning', 'like', "%{$searchTerm}%")
                  ->orWhere('note_evening', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('full_name', 'like', "%{$searchTerm}%")
                                ->orWhere('username', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('full_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->full_name}%");
            });
        }

        if ($request->filled('station') && is_array($request->station) && count($request->station) > 0) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->whereIn('station', $request->station);
            });
        }

        if ($request->filled('position') && is_array($request->position) && count($request->position) > 0) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->whereIn('position', $request->position);
            });
        }

        if ($request->filled('status') && is_array($request->status) && count($request->status) > 0) {
            $statusConditions = [];
            foreach ($request->status as $status) {
                switch ($status) {
                    case 'full_day':
                        $statusConditions[] = function ($q) {
                            $q->whereNotNull('checkin_morning')->whereNotNull('checkin_evening');
                        };
                        break;
                    case 'morning_only':
                        $statusConditions[] = function ($q) {
                            $q->whereNotNull('checkin_morning')->whereNull('checkin_evening');
                        };
                        break;
                    case 'evening_only':
                        $statusConditions[] = function ($q) {
                            $q->whereNull('checkin_morning')->whereNotNull('checkin_evening');
                        };
                        break;
                    case 'no_checkin':
                        $statusConditions[] = function ($q) {
                            $q->whereNull('checkin_morning')->whereNull('checkin_evening');
                        };
                        break;
                }
            }

            if (!empty($statusConditions)) {
                $query->where(function ($q) use ($statusConditions) {
                    foreach ($statusConditions as $condition) {
                        $q->orWhere($condition);
                    }
                });
            }
        }

        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter by user position (same as index)
        switch ($position) {
            case 'department_head':
            case 'office_workers':
                break;
            case 'Station_Chief':
                if ($user->station) {
                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('station', $user->station);
                    });
                }
                break;
            case 'Farm_worker':
                case 'Mechanical_worker':
                $query->where('users_id', $user->id);
                break;
            default:
                $query->whereRaw('1=0');
        }

        // Log SQL query
        // \Log::info('AttendanceLogsController@exportExcel - Final query', [
        //     'sql' => $query->toSql(),
        //     'bindings' => $query->getBindings(),
        //     'filtered_count' => $query->count()
        // ]);

        // Get data - แก้ไขส่วนนี้ให้จัดการ pagination อย่างถูกต้อง
        $all = filter_var($request->get('all', false), FILTER_VALIDATE_BOOLEAN);
        
        if ($all) {
            // Export ทั้งหมด
            $logs = $query->with('user')->orderBy('date', 'desc')->get();
            \Log::info('AttendanceLogsController@exportExcel - Export all data', [
                'total_count' => $logs->count()
            ]);
        } else {
            // Export หน้าปัจจุบัน
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            
            $logs = $query->with('user')
                         ->orderBy('date', 'desc')
                         ->paginate($perPage, ['*'], 'page', $page)
                         ->getCollection();
            
            \Log::info('AttendanceLogsController@exportExcel - Export current page', [
                'page' => $page,
                'per_page' => $perPage,
                'page_count' => $logs->count()
            ]);
        }

        // \Log::info('AttendanceLogsController@exportExcel - Data retrieved', [
        //     'logs_count' => $logs->count(),
        //     'export_type' => $all ? 'all' : 'current_page'
        // ]);

        // Create Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

       // ดึงตำแหน่งทั้งหมดมา mapping
        $positionsMap = \DB::table('listposition')->pluck('position', 'id_position');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // เพิ่ม column "Mã nhân viên" และแก้ไข "Chức vụ" ให้ mapping
        $header = [
            '#', 'Ngày', 'Trạm', 'Tên', 'Mã nhân viên', 'Chức vụ',
            'CheckIn Sáng', 'Ghi chú Sáng', 'CheckIn Chiều', 'Ghi chú Chiều', 'Trạng thái'
        ];
        $sheet->fromArray($header, null, 'A1');

        $rows = [];
        $startIndex = $all ? 1 : (($page - 1) * $perPage) + 1;

        foreach ($logs as $idx => $log) {
            $user = $log->user;
            $status = ($log->checkin_morning && $log->checkin_evening) ? 'Đầy đủ'
                : ($log->checkin_morning ? 'Chỉ sáng'
                : ($log->checkin_evening ? 'Chỉ chiều' : 'Chưa điểm danh'));

            // Mapping position
            $positionValue = '';
            if ($user && $user->position) {
                $positionValue = $positionsMap[$user->position] ?? $user->position;
            }

             // Format Ngày
    $dateFormatted = $log->date ? \Carbon\Carbon::parse($log->date)->format('d/m/Y') : '';

     $checkinMorningFormatted = $log->checkin_morning
    ? \Carbon\Carbon::parse($log->checkin_morning)->setTimezone('Asia/Bangkok')->format('d/m/Y H:i')
    : '';

$checkinEveningFormatted = $log->checkin_evening
    ? \Carbon\Carbon::parse($log->checkin_evening)->setTimezone('Asia/Bangkok')->format('d/m/Y H:i')
    : '';

              $rows[] = [
        $startIndex + $idx,
        $dateFormatted,
        $user->station ?? '',
        $user->full_name ?? '',
        $user->manv_ttca ?? '', // Mã nhân viên
        $positionValue,         // Chức vụ (mapped)
        $checkinMorningFormatted,
        $log->note_morning,
        $checkinEveningFormatted,
        $log->note_evening,
        $status,
    ];
        }

        if (count($rows) > 0) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $exportType = $all ? 'all' : "page_{$page}";
        $filename = 'attendance_logs_' . $exportType . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Export failed: ' . $e->getMessage()
        ], 500);
    }
}



}