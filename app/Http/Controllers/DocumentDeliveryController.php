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

    public function searchBienBan(Request $request) {
        $investmentProject = $request->input('investment_project');
        $creator = $request->input('creator');
        $query = \DB::table('tb_bien_ban_nghiemthu_dv')
            ->where('vu_dau_tu', $investmentProject)
            ->where('can_bo_nong_vu', $creator)
            ->limit(10);
        $results = $query->get();
        return response()->json($results);
    }

    public function addMapping(Request $request) {
        $mapping = DocumentMapping::create([
            'document_code'   => $request->input('document_code'),
            'ma_nghiem_thu_bb'=> $request->input('ma_nghiem_thu_bb')
        ]);
        return response()->json($mapping);
    }





}