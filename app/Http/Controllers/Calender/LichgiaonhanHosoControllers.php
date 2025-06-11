<?php
// filepath: f:\Webpoject\TTCA_PTNL\ttca_ptnl\app\Http\Controllers\Calender\LichgiaonhanHosoControllers.php

namespace App\Http\Controllers\Calender;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LichgiaonhanHosoControllers extends Controller
{
    /**
     * Get calendar events for document delivery tracking
     */
    public function getCalendarEvents(Request $request)
    {
        try {
            $month = $request->get('month', date('n'));
            $year = $request->get('year', date('Y'));
            
            // Calculate date range for the month
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            
            // Fetch calendar events from document_logs
            $calendarEvents = DB::table('document_logs')
                ->select([
                    'document_logs.id',
                    'document_logs.document_id',
                    'document_logs.action_date',
                    'document_logs.action',
                    'document_logs.action_by',
                    'document_logs.comments',
                    'document_delivery.title',
                    'users.full_name as user_name'
                ])
                ->join('document_delivery', 'document_logs.document_id', '=', 'document_delivery.id')
                ->join('users', 'document_logs.action_by', '=', 'users.id')
                ->whereIn('document_logs.action', ['sending', 'received'])
                ->whereBetween('document_logs.action_date', [$startDate, $endDate])
                ->orderBy('document_logs.action_date', 'asc')
                ->get();
            
            // Transform data for calendar
            $events = $calendarEvents->map(function ($log) {
                return [
                    'id' => $log->id,
                    'document_id' => $log->document_id,
                    'date' => Carbon::parse($log->action_date)->format('Y-m-d'),
                    'title' => $log->title ?? 'Không có tiêu đề',
                    'status' => $this->mapActionToStatus($log->action),
                    'user_name' => $log->user_name ?? 'N/A',
                    'description' => $log->comments ?? '',
                    'action' => $log->action
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $events,
                'month' => $month,
                'year' => $year,
                'total' => $events->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu lịch giao nhận hồ sơ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document transactions for tracking section
     */
    public function getDocumentTransactions(Request $request)
    {
        try {
            $month = $request->get('month', date('n'));
            $year = $request->get('year', date('Y'));
            
            // Calculate date range for the month
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            
            // Fetch document transactions
            $transactions = DB::table('document_logs')
                ->select([
                    'document_logs.id',
                    'document_logs.document_id',
                    'document_logs.action_date as date',
                    'document_logs.action',
                    'document_logs.action_by',
                    'document_logs.comments as description',
                    'document_delivery.title',
                    'users.full_name as user_send'
                ])
                ->join('document_delivery', 'document_logs.document_id', '=', 'document_delivery.id')
                ->join('users', 'document_logs.action_by', '=', 'users.id')
                ->whereIn('document_logs.action', ['sending', 'received'])
                ->whereBetween('document_logs.action_date', [$startDate, $endDate])
                ->orderBy('document_logs.action_date', 'desc')
                ->get();
            
            // Transform data for tracking list
            $documentTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'title' => $transaction->title ?? 'Không có tiêu đề',
                    'date' => Carbon::parse($transaction->date)->format('Y-m-d'),
                    'userSend' => $transaction->user_send ?? 'N/A',
                    'status' => $this->mapActionToStatus($transaction->action),
                    'description' => $transaction->description ?? ''
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $documentTransactions,
                'month' => $month,
                'year' => $year,
                'total' => $documentTransactions->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu giao dịch hồ sơ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map action to Vietnamese status
     */
    private function mapActionToStatus($action)
    {
        switch ($action) {
            case 'sending':
                return 'Đang nộp';
            case 'received':
                return 'Đã nhận';
            default:
                return 'Không xác định';
        }
    }
}