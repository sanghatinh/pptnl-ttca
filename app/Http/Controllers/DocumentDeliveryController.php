<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use App\Models\DocumentDelivery;
use App\Models\DocumentMapping;
use App\Models\DocumentMappingHomGiong;
use App\Models\BienBanNghiemThuHomGiong;
use App\Models\Log\DocumentLog; // Add this import

use Illuminate\Http\Request;

class DocumentDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentDelivery::with(['creator', 'receiver']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('per_page', 10);
        $documentDeliveries = $query->paginate($perPage);

        return response()->json($documentDeliveries);
    }


    public function store(Request $request) {
        try {
            $investmentName = $request->input('investment_project');
            $documentCode = DocumentDelivery::generateDocumentCode($investmentName);
            
            $document = DocumentDelivery::create([
                'document_code'      => $documentCode,
                'created_date'       => $request->input('created_date'),
                'title'             => 'PGNHS-'  . $request->input('document_type') . '-' . $documentCode,
                'investment_project' => $investmentName, // ยังเก็บชื่อเต็มไว้
                'creator_id'         => $request->input('creator_id'),
                'station'           => $request->input('station'),
                'document_type'      => $request->input('document_type'),
                'receiver_id'        => $request->input('receiver_id'),
                'received_date'      => $request->input('received_date'),
                'file_count'        => $request->input('file_count'),
                'loan_status'       => $request->input('loan_status'),
                'status'            => 'creating'
            ]);

            // Log the create action
            // Log the create action - action_by จะเป็น creator_id
        DocumentLog::create([
            'document_id' => $document->id,
            'action' => 'creating',
            'action_by' => $request->input('creator_id'), // ID ของ user ที่สร้างเอกสาร
            'action_date' => now(),
        ]);
    
            return response()->json($document);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error creating document: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateStatus(Request $request, $id) {
        $document = DocumentDelivery::findOrFail($id);
        
        // Update document status
        $document->update([
            'status' => $request->input('status'),
            'received_date' => $request->input('received_date'), 
            'receiver_id' => $request->input('receiver_id'),
        ]);
    
        // Log the status change - action_by จะเป็น ID ของ user ที่ทำการเปลี่ยนสถานะ
        DocumentLog::create([
            'document_id' => $document->id,
            'action' => $request->input('status'),
            'action_by' => $request->input('receiver_id'), // ID ของ user ที่เปลี่ยนสถานะ
            'action_date' => now(),
        ]);
    
        return response()->json($document);
    }


    public function update(Request $request, $id) {
        $document = DocumentDelivery::findOrFail($id);
        $document->update($request->all());
    
        // Log the update action - action_by จะเป็น creator_id
        DocumentLog::create([
            'document_id' => $document->id,
            'action' => $document->status,
            'action_by' => $request->input('creator_id'), // ID ของ user ที่แก้ไขเอกสาร
            'action_date' => now(),
        ]);
    
        return response()->json($document);
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
        $search = $request->input('search');
        $investmentProject = $request->input('investment_project');
        $station = $request->input('station');
        
        $query = \DB::table('tb_bien_ban_nghiemthu_dv')
            ->where('vu_dau_tu', $investmentProject)
            ->where('tram', $station);
    
        if ($search) {
            $query->where('ma_nghiem_thu', 'LIKE', "%{$search}%");
        }
            
        $results = $query->limit(10)->get();
        
        \Log::info('Search params:', [
            'search' => $search,
            'investment_project' => $investmentProject,
            'station' => $station
        ]);
        \Log::info('Results:', ['count' => $results->count(), 'data' => $results]);
        
        return response()->json($results);
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
            $search = $request->input('search');
            $investmentProject = $request->input('investment_project');
            
            $query = \DB::table('bien_ban_nghiem_thu_hom_giong')
                ->where('vu_dau_tu', $investmentProject);
    
            if ($search) {
                $query->where('ma_so_phieu', 'LIKE', "%{$search}%");
            }
                
            $results = $query->limit(10)->get();
            
            \Log::info('Search params:', [
                'search' => $search,
                'investment_project' => $investmentProject
            ]);
            \Log::info('Results:', ['count' => $results->count(), 'data' => $results]);
            
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



}