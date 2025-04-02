<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\DocumentDelivery;
use Illuminate\Support\Facades\Log;

class CheckDocumentAccess
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // ตรวจสอบว่ามีการล็อกอินหรือไม่
            if (!auth()->check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            
            $user = auth()->user();
            $documentId = $request->route('id');
            
            if (!$documentId) {
                return response()->json(['message' => 'Invalid document ID'], 400);
            }
            
            $document = DocumentDelivery::find($documentId);
            
            if (!$document) {
                return response()->json(['message' => 'Document not found'], 404);
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
                    $hasAccess = $document->creator_id === $user->id;
                    break;
                
                default:
                    $hasAccess = false;
            }
            
            if (!$hasAccess) {
                Log::warning("Unauthorized access attempt: User {$user->id} ({$user->position}) tried to access document {$documentId}");
                return response()->json(['message' => 'You do not have permission to access this document'], 403);
            }
            
            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('Error in CheckDocumentAccess middleware: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
    }
}