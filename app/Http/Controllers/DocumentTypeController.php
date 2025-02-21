<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentType;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $types = DocumentType::all();
        return response()->json($types);
    }
}