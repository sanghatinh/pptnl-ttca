<?php

namespace App\Http\Controllers;

use App\Models\DocumentDelivery;
use App\Models\DocumentMapping;
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
        $investmentCode = $request->input('investment_project');
        $documentCode = DocumentDelivery::generateDocumentCode($investmentCode);
        $document = DocumentDelivery::create([
            'document_code'      => $documentCode,
            'created_date'       => $request->input('created_date'),
            'title'              => 'PGNHS-' . $request->input('creator_name') . '-' . $request->input('document_type') . '-' . $documentCode,
            'investment_project' => $investmentCode,
            'creator_id'         => $request->input('creator_id'),
            'station'            => $request->input('station'),
            'document_type'      => $request->input('document_type'),
            'receiver_id'        => $request->input('receiver_id'),
            'received_date'      => $request->input('received_date'),
            'file_count'         => $request->input('file_count'),
            'loan_status'        => $request->input('loan_status'),
            'status'             => 'creating'
        ]);
        return response()->json($document);
    }


    public function updateStatus(Request $request, $id) {
        $document = DocumentDelivery::findOrFail($id);
        $document->update([
            'status' => $request->input('status'),
            'received_date' => $request->input('received_date'),
            'receiver_id' => $request->input('receiver_id'),
        ]);
        return response()->json($document);
    }

    public function update(Request $request, $id)
{
    $document = DocumentDelivery::findOrFail($id);
    $document->update($request->all());
    return response()->json($document);
}

public function destroy($id)
{
    try {
        // Find the document
        $document = DocumentDelivery::findOrFail($id);
        
        // Delete any associated mappings first
        DocumentMapping::where('document_code', $document->document_code)->delete();
        
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
        // Check if mapping already exists
        $existingMapping = \App\Models\DocumentMapping::where('ma_nghiem_thu_bb', $request->input('ma_nghiem_thu'))->first();
        
        if ($existingMapping) {
            // If mapping exists, check if the document is cancelled
            $document = DocumentDelivery::where('document_code', $existingMapping->document_code)
                ->first();
                
            if (!$document || $document->status !== 'cancelled') {
                return response()->json([
                    'error' => 'Mapping already exists and associated document is not cancelled'
                ], 422);
            }
        }
    
        // If we get here, either the mapping doesn't exist or the associated document is cancelled
        $mapping = \App\Models\DocumentMapping::create([
            'document_code' => $request->input('document_code'),
            'ma_nghiem_thu_bb' => $request->input('ma_nghiem_thu')
        ]);
    
        return response()->json($mapping);
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



}