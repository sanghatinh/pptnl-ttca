<?php

namespace App\Http\Controllers;

use App\Models\DocumentDelivery;
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
}