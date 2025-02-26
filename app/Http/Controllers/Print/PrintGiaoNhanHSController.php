<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentDelivery;
use App\Models\DocumentMapping;
use App\Models\DocumentMappingHomgiong;
use App\Models\Log\DocumentLog;
use App\Models\User;  // Make sure this points to the correct User model namespace

class PrintGiaoNhanHSController extends Controller
{
    
    public function print($document_code)
    {
        try {
            // Get document with related data
            $document = DocumentDelivery::with([
                'creator',
                'receiver',
                'documentMappings.bienBanNghiemThu',
                'documentMappingsHomgiong.bienBanHomGiong'
            ])->where('document_code', $document_code)->first();
    
            if (!$document) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document not found'
                ], 404);
            }
    
            // Only add receiver info if status is not 'creating' or 'sending'
            if (!in_array($document->status, ['creating', 'sending'])) {
                // Get receiver info from DocumentLog
                $receiverInfo = DocumentLog::where('document_id', $document->id)
                    ->where('action', 'received')
                    ->with('actionUser')
                    ->latest()
                    ->first();
    
                // Add receiver information to document
                $document->receiver_info = [
                    'name' => $document->receiver ? $document->receiver->full_name : 
                             ($receiverInfo ? $receiverInfo->actionUser->full_name : null),
                    'date' => $document->received_date ?? 
                             ($receiverInfo ? $receiverInfo->created_at : null)
                ];
            } else {
                // Set empty receiver info for creating/sending status
                $document->receiver_info = [
                    'name' => null,
                    'date' => null
                ];
            }
    
            $nghiemThuDocuments = $document->documentMappings;
            $homGiongDocuments = $document->documentMappingsHomgiong;
    
            return view('Print.PrintgiaonhanHS', compact('document', 'nghiemThuDocuments', 'homGiongDocuments'));
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}