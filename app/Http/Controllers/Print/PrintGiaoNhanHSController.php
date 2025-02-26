<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentDelivery;
use App\Models\DocumentMapping;
use App\Models\DocumentMappingHomgiong;

class PrintGiaoNhanHSController extends Controller
{
    public function print($document_code)
    {
        try {
            // ดึงข้อมูลเอกสารพร้อมความสัมพันธ์ที่เกี่ยวข้อง
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
    
            $nghiemThuDocuments = $document->documentMappings;
            $homGiongDocuments = $document->documentMappingsHomgiong;
    
            // ส่งข้อมูลไปยัง view
            return view('Print.PrintgiaonhanHS', compact('document', 'nghiemThuDocuments', 'homGiongDocuments'));
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}