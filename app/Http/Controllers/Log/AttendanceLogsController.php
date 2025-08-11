<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log\AttendanceLogs;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceLogsController extends Controller
{
    /**
     * แสดงรายการลงเวลา
     */
     public function index(Request $request)
    {
        $query = AttendanceLogs::query();

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
        if ($user) {
            $position = $user->userPosition ?? null;
            if (in_array($position, ['Station_Chief', 'Farm_worker'])) {
                $query->where('users_id', $user->id);
            }
            // department_head และ office_workers เห็นทั้งหมด ไม่ต้องกรอง
        }

        $logs = $query->with('user')->orderBy('date', 'desc')->paginate(20);

        // เพิ่มการคำนวณ Status ตามเงื่อนไขที่ระบุ
        $logs->getCollection()->transform(function ($log) {
            if ($log->checkin_morning && $log->checkin_evening) {
                $log->status = "Đã làm cả ngày";
            } elseif ($log->checkin_morning) {
                $log->status = "Đã làm buổi sáng";
            } elseif ($log->checkin_evening) {
                $log->status = "Đã làm buổi chiều";
            } else {
                $log->status = null;
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

    // Ensure users_id matches authenticated user (for security)
    if ($data['users_id'] != $user->id) {
        \Log::warning('AttendanceLogsController@store - User ID mismatch', [
            'authenticated_user' => $user->id,
            'submitted_user_id' => $data['users_id']
        ]);
        // You might want to allow this for admins, but for now, enforce same user
        $data['users_id'] = $user->id;
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

        $validator = Validator::make($request->all(), AttendanceLogs::rules($id));
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'note_morning', 'note_evening',
            'lat_morning', 'lng_morning', 'lat_evening', 'lng_evening'
        ]);

        $cloudinaryService = new CloudinaryService();

        // อัปเดตรูปเช้า
        if ($request->hasFile('photo_morning')) {
            $result = $cloudinaryService->uploadImageOptimized(
                $request->file('photo_morning'),
                [
                    'folder' => 'attendance_logs',
                    'quality' => 'auto:good',
                    'format' => 'auto',
                    'transformation' => [
                        'width' => 1200,
                        'height' => 1200,
                        'crop' => 'limit',
                        'quality' => 'auto:good',
                        'fetch_format' => 'auto'
                    ],
                    'max_bytes' => 300 * 1024
                ]
            );
            if ($result['success']) {
                $data['photo_morning'] = $result['public_id'];
                $data['checkin_morning'] = now();
            } else {
                return response()->json(['success' => false, 'message' => $result['message'] ?? 'Upload failed'], 500);
            }
        }

        // อัปเดตรูปเย็น
        if ($request->hasFile('photo_evening')) {
            $result = $cloudinaryService->uploadImageOptimized(
                $request->file('photo_evening'),
                [
                    'folder' => 'attendance_logs',
                    'quality' => 'auto:good',
                    'format' => 'auto',
                    'transformation' => [
                        'width' => 1200,
                        'height' => 1200,
                        'crop' => 'limit',
                        'quality' => 'auto:good',
                        'fetch_format' => 'auto'
                    ],
                    'max_bytes' => 300 * 1024
                ]
            );
            if ($result['success']) {
                $data['photo_evening'] = $result['public_id'];
                $data['checkin_evening'] = now();
            } else {
                return response()->json(['success' => false, 'message' => $result['message'] ?? 'Upload failed'], 500);
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
}