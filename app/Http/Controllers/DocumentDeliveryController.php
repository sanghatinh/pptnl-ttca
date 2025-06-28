<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use App\Models\DocumentDelivery;
use App\Models\DocumentMapping;
use App\Models\DocumentMappingHomgiong;
use App\Models\BienBanNghiemThuHomGiong;
use App\Models\Log\DocumentLog; // Add this import
use App\Models\Log\StatusDocumentDelivery; // Add this import
use App\Models\QuanlyHS\PaymentRequestLog;

use Illuminate\Http\Request;

class DocumentDeliveryController extends Controller
{


    
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = DocumentDelivery::with([
            'creator', 
            'receiver',
            'logs' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ]);
    
        // Apply role-based filtering
        switch ($user->position) {
            case 'department_head':
            case 'office_workers':
                // Can see all records
                break;
                
            case 'Station_Chief':
                // Can only see records from their station
                $query->where('station', $user->station);
                break;
                
            case 'Farm_worker':
                // Can only see records they created
                $query->where('creator_id', $user->id);
                break;
                
            default:
                // Restrict access for unknown roles
                return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('document_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
    
        // Apply status filter if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
    
        // Get status mapping for display
        $statusMapping = StatusDocumentDelivery::pluck('name_status', 'id_status')
            ->toArray();
    
        $documentDeliveries = $query->get()->map(function($doc) use ($statusMapping) {
            $creatingLog = $doc->logs->where('action', 'creating')->first();
            $receivedLog = $doc->logs->where('action', 'received')->first();
            
            return [
                'id' => $doc->id,
                'document_code' => $doc->document_code,
                'station' => $doc->station,
                'created_date' => $creatingLog ? $creatingLog->action_date : $doc->created_date,
                'title' => $doc->title,
                'investment_project' => $doc->investment_project,
                'file_count' => $doc->file_count,
                'document_type' => $doc->document_type,
                'creator' => $creatingLog ? $creatingLog->actionUser : $doc->creator,
                'receiver' => $receivedLog ? $receivedLog->actionUser : null,
                'received_date' => $receivedLog ? $receivedLog->action_date : null,
                'status' => [
                    'code' => $doc->status,
                    'name' => $statusMapping[$doc->status] ?? $doc->status
                ],
                // 'loan_status' => $doc->loan_status
            ];
        });
    
        return response()->json(['data' => $documentDeliveries]);
    }


    public function show($id)
    {
        try {
            $document = DocumentDelivery::with(['creator', 'receiver'])->findOrFail($id);
            return response()->json($document);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request) {
        try {
            $investmentName = $request->input('investment_project');
            $documentCode = DocumentDelivery::generateDocumentCode($investmentName);
     
            
            $document = DocumentDelivery::create([
            'document_code'      => $documentCode,
            'created_date'       => $request->input('created_date'),
           'title'             => 'PGNHS-' . $request->input('document_type') . '-' . str_replace('PGNHS-', '', $documentCode),
            'investment_project' => $investmentName, // ยังเก็บชื่อเต็มไว้
            'creator_id'         => $request->input('creator_id'),
            'station'           => $request->input('station'),
            'document_type'      => $request->input('document_type'),
            'receiver_id'        => null, // เปลี่ยนจาก $request->input('receiver_id') เป็น null
            'received_date'      => null, // เปลี่ยนจาก $request->input('received_date') เป็น null
            'file_count'        => $request->input('file_count'),
            // 'loan_status'       => $request->input('loan_status'),
            'status'            => 'creating',
        ]);

            // Log the create action
            // Log the create action - action_by จะเป็น creator_id
        DocumentLog::create([
            'document_id' => $document->id,
            'action' => 'creating',
            'action_by' => $request->input('creator_id'), // ID ของ user ที่สร้างเอกสาร
            'action_date' => $request->input('created_date'), // ใช้วันที่ที่ผู้ใช้เลือก
            'comments' => "Document was created",

        ]);
    
            return response()->json($document);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error creating document: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateStatus(Request $request, $id)
{
    try {
        $document = DocumentDelivery::findOrFail($id);
        
        $status = $request->input('status');
        $receiverId = $request->input('receiver_id');
        $actionDate = $request->input('action_date');
        //update file_count
        $document->file_count = $request->input('file_count');
        

        // Update document status
        $document->status = $status;
      

        
        // Set receiver_id when status is 'received'
        if ($status === 'received') {
            $document->receiver_id = $receiverId;
            $document->received_date = $request->input('received_date');
        }

        
        $document->save();

        // Create log entry
        DocumentLog::create([
            'document_id' => $document->id,
            'action' => $status,
            'action_by' => $receiverId,
            'action_date' => $actionDate,
            'comments' => "Document status changed to {$status}"
        ]);

        // Return updated document with relationships
        return response()->json(
            DocumentDelivery::with(['creator', 'receiver'])
                ->findOrFail($document->id)
        );

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function update(Request $request, $id) {
    try {
        $document = DocumentDelivery::findOrFail($id);
        $document->update($request->all());

        // Find existing log entry and update it
        $documentLog = DocumentLog::where('document_id', $document->id)
            ->where('action', 'creating')
            ->first();

        if ($documentLog) {
            // Update existing log entry
            $documentLog->update([
                'action' => $request->input('action'),
                'action_by' => $request->input('action_by'),
                'action_date' => $request->input('action_date'),
                'comments' => "Document was updated"
            ]);
        } else {
            // Create new log entry if none exists (fallback)
            DocumentLog::create([
                'document_id' => $document->id,
                'action' => $request->input('action'),
                'action_by' => $request->input('action_by'),
                'action_date' => $request->input('action_date'),
                'comments' => "Document was updated"
            ]);
        }

        return response()->json($document);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error updating document: ' . $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Find the document
        $document = DocumentDelivery::findOrFail($id);
        
        // Delete any associated mappings first
        DocumentMapping::where('document_code', $document->document_code)->delete();

        // Delete any associated mappings first for hom giong
        DocumentMappingHomGiong::where('document_code', $document->document_code)->delete();
        
        
        // Delete the document
        $document->delete();
        
        return response()->json([
            'message' => 'Document deleted successfully'
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error deleting document',
            'error' => $e->getMessage()
        ], 500);
    }
}

  public function searchBienBanNgheThu(Request $request)
{
    try {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $search = $request->input('search');
        $investmentProject = $request->input('investment_project');
        $station = $request->input('station');
        
        $query = \DB::table('tb_bien_ban_nghiemthu_dv')
            ->where('vu_dau_tu', $investmentProject);

        // ไม่แสดงรายการที่ ma_nghiem_thu มีอยู่ใน document_mapping
        $mappedMaNghiemThu = \DB::table('document_mapping')->pluck('ma_nghiem_thu_bb')->toArray();
        if (!empty($mappedMaNghiemThu)) {
            $query->whereNotIn('ma_nghiem_thu', $mappedMaNghiemThu);
        }

        // Apply role-based filtering
        switch ($user->position) {
            case 'department_head':
            case 'office_workers':
                // Can access all records - no additional filtering needed
                break;
            case 'Station_Chief':
            case 'Farm_worker':
                // Can only access records from their own station
                $query->where('tram', $user->station);
                break;
            default:
                // Unknown role - restrict access
                return response()->json(['error' => 'Unauthorized position'], 403);
        }

        if ($search) {
            $query->where('ma_nghiem_thu', 'LIKE', "%{$search}%");
        }
            
        $results = $query->limit(10)->get();
        
        \Log::info('Search params:', [
            'search' => $search,
            'investment_project' => $investmentProject,
            'station' => $station,
            'user_position' => $user->position,
            'user_station' => $user->station
        ]);
        \Log::info('Results:', ['count' => $results->count()]);
        
        return response()->json($results);
        
    } catch (\Exception $e) {
        \Log::error('Error in searchBienBanNgheThu: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function addMapping(Request $request) {
        try {
            // Check if mapping already exists
            $existingMapping = \App\Models\DocumentMapping::where('ma_nghiem_thu_bb', $request->input('ma_nghiem_thu'))->first();
            
            if ($existingMapping) {
                // If mapping exists, check if the document is cancelled
                $document = DocumentDelivery::where('document_code', $existingMapping->document_code)
                    ->first();
                    
                if (!$document || $document->status !== 'cancelled') {
                    return response()->json([
                        'status' => 'error',
                        'error' => 'biên bản đã tồn tại'
                    ], 422);
                }
            }
        
            // If we get here, either the mapping doesn't exist or the associated document is cancelled
            $mapping = \App\Models\DocumentMapping::create([
                'document_code' => $request->input('document_code'),
                'ma_nghiem_thu_bb' => $request->input('ma_nghiem_thu')
            ]);
        
            return response()->json([
                'status' => 'success',
                'data' => $mapping,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getMappings($documentCode) {
        $mappings = \DB::table('document_mapping')
            ->join('tb_bien_ban_nghiemthu_dv', 'document_mapping.ma_nghiem_thu_bb', '=', 'tb_bien_ban_nghiemthu_dv.ma_nghiem_thu')
            ->where('document_mapping.document_code', $documentCode)
            ->select(
                'tb_bien_ban_nghiemthu_dv.*', 
                'document_mapping.id as mapping_id'
            )
            ->get();
        
        return response()->json($mappings);
    }
    
    public function deleteMapping($id) {
        $mapping = DocumentMapping::findOrFail($id);
        $mapping->delete();
        return response()->json(['message' => 'Mapping deleted successfully']);
    }

    //Modal add hom giong tao phieu giao nhan
 public function searchBienBanHomGiong(Request $request)
{
    try {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $search = $request->input('search');
        $investmentProject = $request->input('investment_project');
        $station = $request->input('station');
        
        $query = \DB::table('bien_ban_nghiem_thu_hom_giong')
            ->where('vu_dau_tu', $investmentProject);

        // ไม่แสดงรายการที่ ma_so_phieu มีอยู่ใน document_mapping_homgiong
        $mappedMaSoPhieu = \DB::table('document_mapping_homgiong')->pluck('ma_so_phieu')->toArray();
        if (!empty($mappedMaSoPhieu)) {
            $query->whereNotIn('ma_so_phieu', $mappedMaSoPhieu);
        }

        // Apply role-based filtering
        switch ($user->position) {
            case 'department_head':
            case 'office_workers':
                // Can access all records from all stations - no filtering needed
                break;
            case 'Station_Chief':
            case 'Farm_worker':
                // Can only access records from their own station
                $query->where('tram', $user->station);
                break;
            default:
                // Unknown role - restrict access
                return response()->json(['error' => 'Unauthorized position'], 403);
        }

        if ($search) {
            $query->where('ma_so_phieu', 'LIKE', "%{$search}%");
        }
            
        $results = $query->limit(10)->get();
        
        \Log::info('Search params:', [
            'search' => $search,
            'investment_project' => $investmentProject,
            'station' => $station,
            'user_position' => $user->position,
            'user_station' => $user->station
        ]);
        \Log::info('Results:', ['count' => $results->count()]);
        
        return response()->json($results);

    } catch (\Exception $e) {
        \Log::error('Error in searchBienBanHomGiong: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function addMappingHomGiong(Request $request)
{
    try {
        // Check if mapping already exists
        $existingMapping = DocumentMappingHomGiong::where('ma_so_phieu', $request->input('ma_so_phieu'))->first();

        if ($existingMapping) {
            // If mapping exists, check if the document is cancelled
            $document = DocumentDelivery::where('document_code', $existingMapping->document_code)->first();

            if (!$document || $document->status !== 'cancelled') {
                return response()->json([
                    'error' => 'biên bản đã tồn tại'
                ], 422);
            }
        }

        // If we get here, either the mapping doesn't exist or the associated document is cancelled
        $mapping = DocumentMappingHomGiong::create([
            'document_code' => $request->input('document_code'),
            'ma_so_phieu'   => $request->input('ma_so_phieu')
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $mapping
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getMappingsHomGiong($documentCode)
{
    $mappings = \DB::table('document_mapping_homgiong')
        ->join('bien_ban_nghiem_thu_hom_giong', 'document_mapping_homgiong.ma_so_phieu', '=', 'bien_ban_nghiem_thu_hom_giong.ma_so_phieu')
        ->where('document_mapping_homgiong.document_code', $documentCode)
        ->select(
            'bien_ban_nghiem_thu_hom_giong.*', 
            'document_mapping_homgiong.id as mapping_id'
        )
        ->get();
    
    return response()->json($mappings);
}

public function deleteMappingHomGiong($id)
{
    $mapping = DocumentMappingHomGiong::findOrFail($id);
    $mapping->delete();
    return response()->json(['message' => 'Mapping deleted successfully']);
}

// ลบ ได้หลายรายการ ในหน้ารายการเอกสาร
public function bulkDelete(Request $request) 
{
    try {
        $ids = $request->input('ids');
        
        // Start transaction
        DB::beginTransaction();
        
        // Get all document codes that will be deleted
        $documentCodes = DocumentDelivery::whereIn('id', $ids)
            ->pluck('document_code')
            ->toArray();
            
        // Delete associated mappings
        DocumentMapping::whereIn('document_code', $documentCodes)->delete();
        DocumentMappingHomGiong::whereIn('document_code', $documentCodes)->delete();
        
        // Delete the documents
        DocumentDelivery::whereIn('id', $ids)->delete();
        
        DB::commit();

        return response()->json([
            'message' => 'Documents deleted successfully',
            'count' => count($ids)
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error deleting documents',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function getDocumentInfo($id)
{
    try {
        $document = DocumentDelivery::with(['creator', 'receiver'])->findOrFail($id);
        
        // Get logs
        $creatorLog = DocumentLog::where('document_id', $document->id)
            ->where('action', 'creating')
            ->with('actionUser')
            ->first();
            
        $receiverLog = DocumentLog::where('document_id', $document->id)
            ->whereIn('action', ['received', 'cancelled']) 
            ->with('actionUser')
            ->latest()
            ->first();

        return response()->json([
            'creator' => $creatorLog ? $creatorLog->actionUser : $document->creator,
            'receiver' => $receiverLog ? $receiverLog->actionUser : $document->receiver,
            'created_date' => $creatorLog ? $creatorLog->action_date : $document->created_date,
            'received_date' => $receiverLog ? $receiverLog->action_date : $document->received_date
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function checkAccess($id)
{
    try {
        \Log::info("Checking access for document ID: {$id}");
        $user = auth()->user();
        
        if (!$user) {
            \Log::warning("Unauthorized access: No user authenticated");
            return response()->json(['hasAccess' => false, 'message' => 'User not authenticated'], 401);
        }
        
        $document = DocumentDelivery::find($id);
        
        if (!$document) {
            \Log::warning("Document not found: {$id}");
            return response()->json(['hasAccess' => false, 'message' => 'Document not found'], 404);
        }
    
        $hasAccess = false;
        
        switch ($user->position) {
            case 'department_head':
            case 'office_workers':
                $hasAccess = true;
                break;
                
            case 'Station_Chief':
                $hasAccess = $document->station === $user->station;
                break;
                
            case 'Farm_worker':
                $hasAccess = $document->creator_id == $user->id;
                break;
                
            default:
                $hasAccess = false;
        }
        
        \Log::info("Access result for user {$user->id} ({$user->position}): " . ($hasAccess ? "Allowed" : "Denied"));
        
        return response()->json(['hasAccess' => $hasAccess]);
        
    } catch (\Exception $e) {
        \Log::error('Error in checkAccess: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return response()->json(['hasAccess' => false, 'message' => 'Error checking access', 'error' => $e->getMessage()], 500);
    }
}

 /**
 * ค้นหารายการบันทึกการตรวจรับสำหรับการเพิ่มเข้าใบเบิกเงิน
 */
public function searchBienBanNgheThu_PTTT(Request $request)
{
    try {
        $searchTerm = $request->get('search', '');
        
        // ลองค้นหาอย่างง่ายที่สุดก่อน
        $results = DB::table('tb_bien_ban_nghiemthu_dv')
            ->where('ma_nghiem_thu', 'like', "%{$searchTerm}%")
            ->limit(10)
            ->get();
            
        Log::info("Simple search results count: " . count($results));
        
        return response()->json($results);
    } catch (\Exception $e) {
        Log::error('Error searching for receipts: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to search for receipts', 'message' => $e->getMessage()], 500);
    }
}


/**
 * Search bien ban nghiem thu hom giong for payment request creation
 */
public function searchBienBanHomGiong_PTTT(Request $request)
{
    try {
        $search = $request->get('search', '');
        $investmentProject = $request->get('investment_project', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }
        
        $query = DB::table('bien_ban_nghiem_thu_hom_giong')
            ->select(
                'ma_so_phieu',
                'tram',
                'vu_dau_tu',
                'ten_phieu',
                'hop_dong_dau_tu_mia_ben_giao_hom',
                'tong_thuc_nhan',
                'tong_tien'
            )
            ->where(function($q) use ($search) {
                $q->where('ma_so_phieu', 'LIKE', '%' . $search . '%')
                  ->orWhere('ten_phieu', 'LIKE', '%' . $search . '%')
                  ->orWhere('tram', 'LIKE', '%' . $search . '%');
            });
            
        // Filter by investment project if provided
        
        
        // Add condition to exclude already processed records if needed
        // You can add additional conditions here
        
        $results = $query->limit(10)->get();
        
        return response()->json($results);
        
    } catch (\Exception $e) {
        Log::error('Error searching bien ban nghiem thu hom giong for PTTT: ' . $e->getMessage());
        
        return response()->json([
            'error' => 'Search failed',
            'message' => $e->getMessage()
        ], 500);
    }
}


}