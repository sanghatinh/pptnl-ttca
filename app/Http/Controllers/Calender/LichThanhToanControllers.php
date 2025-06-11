<?php
// filepath: f:\Webpoject\TTCA_PTNL\ttca_ptnl\app\Http\Controllers\Calender\LichThanhToanControllers.php

namespace App\Http\Controllers\Calender;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LichThanhToanControllers extends Controller
{
    /**
     * Get calendar events for payment schedule
     */
    public function getCalendarEvents(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            
            // Calculate date range for the month
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            // Get service payment events
            $serviceEvents = DB::table('Action_phieu_trinh_thanh_toan as apt')
                ->join('tb_phieu_trinh_thanh_toan as tpt', 'apt.ma_trinh_thanh_toan', '=', 'tpt.ma_trinh_thanh_toan')
                ->select(
                    'apt.id',
                    'apt.action',
                    'apt.action_date',
                    'apt.ma_trinh_thanh_toan',
                    'tpt.vu_dau_tu',
                    'tpt.so_dot_thanh_toan',
                    'tpt.tong_tien_thanh_toan as amount',
                    DB::raw("'service' as type"),
                    DB::raw("CONCAT('Thanh toán tiền dịch vụ ', tpt.vu_dau_tu, ' đợt ', tpt.so_dot_thanh_toan) as title")
                )
                ->whereBetween('apt.action_date', [$startDate, $endDate])
                ->whereIn('apt.action', ['processing', 'submitted', 'paid'])
                ->get();
            
            // Get seed payment events
            $seedEvents = DB::table('action_phieu_trinh_thanh_toan_homgiong as apth')
                ->join('tb_phieu_trinh_thanh_toan_homgiong as tpth', 'apth.ma_trinh_thanh_toan', '=', 'tpth.ma_trinh_thanh_toan')
                ->select(
                    'apth.id',
                    'apth.action',
                    'apth.action_date',
                    'apth.ma_trinh_thanh_toan',
                    'tpth.vu_dau_tu',
                    'tpth.so_dot_thanh_toan',
                    'tpth.tong_tien_thanh_toan as amount',
                    DB::raw("'seed' as type"),
                    DB::raw("CONCAT('Thanh toán tiền hom giống ', tpth.vu_dau_tu, ' đợt ', tpth.so_dot_thanh_toan) as title")
                )
                ->whereBetween('apth.action_date', [$startDate, $endDate])
                ->whereIn('apth.action', ['processing', 'submitted', 'paid'])
                ->get();
            
            // Combine and format events
            $allEvents = collect($serviceEvents)->concat($seedEvents)->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => Carbon::parse($event->action_date)->format('Y-m-d'),
                    'amount' => (float) $event->amount,
                    'status' => $this->translateStatus($event->action),
                    'type' => $event->type,
                    'ma_trinh_thanh_toan' => $event->ma_trinh_thanh_toan,
                    'vu_dau_tu' => $event->vu_dau_tu,
                    'so_dot_thanh_toan' => $event->so_dot_thanh_toan
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $allEvents,
                'month' => $month,
                'year' => $year
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu lịch thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Translate status from English to Vietnamese
     */
    private function translateStatus($status)
    {
        $statusMap = [
            'processing' => 'Đang xử lý',
            'submitted' => 'Đã nộp kế toán',
            'paid' => 'Đã thanh toán'
        ];
        
        return $statusMap[$status] ?? $status;
    }


    /**
     * Get tracking data for service payments
     */
    public function getServicePaymentTracking(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            
            // Calculate date range for the month
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            $servicePayments = DB::table('Action_phieu_trinh_thanh_toan as apt')
                ->join('tb_phieu_trinh_thanh_toan as tpt', 'apt.ma_trinh_thanh_toan', '=', 'tpt.ma_trinh_thanh_toan')
                ->select(
                    'apt.id',
                    'apt.action',
                    'apt.action_date',
                    'apt.ma_trinh_thanh_toan',
                    'tpt.vu_dau_tu',
                    'tpt.so_dot_thanh_toan',
                    'tpt.tong_tien_thanh_toan as amount',
                    DB::raw("CONCAT('Thanh toán tiền dịch vụ ', tpt.vu_dau_tu, ' đợt ', tpt.so_dot_thanh_toan) as title")
                )
                ->whereBetween('apt.action_date', [$startDate, $endDate])
                ->whereIn('apt.action', ['processing', 'submitted', 'paid'])
                ->orderBy('apt.action_date', 'desc')
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'title' => $payment->title,
                        'date' => Carbon::parse($payment->action_date)->format('Y-m-d'),
                        'amount' => (float) $payment->amount,
                        'status' => $this->translateStatus($payment->action),
                        'type' => 'service',
                        'ma_trinh_thanh_toan' => $payment->ma_trinh_thanh_toan,
                        'vu_dau_tu' => $payment->vu_dau_tu,
                        'so_dot_thanh_toan' => $payment->so_dot_thanh_toan
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $servicePayments
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu thanh toán dịch vụ: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get tracking data for seed payments
     */
    public function getSeedPaymentTracking(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            
            // Calculate date range for the month
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            $seedPayments = DB::table('action_phieu_trinh_thanh_toan_homgiong as apth')
                ->join('tb_phieu_trinh_thanh_toan_homgiong as tpth', 'apth.ma_trinh_thanh_toan', '=', 'tpth.ma_trinh_thanh_toan')
                ->select(
                    'apth.id',
                    'apth.action',
                    'apth.action_date',
                    'apth.ma_trinh_thanh_toan',
                    'tpth.vu_dau_tu',
                    'tpth.so_dot_thanh_toan',
                    'tpth.tong_tien_thanh_toan as amount',
                    DB::raw("CONCAT('Thanh toán tiền hom giống ', tpth.vu_dau_tu, ' đợt ', tpth.so_dot_thanh_toan) as title")
                )
                ->whereBetween('apth.action_date', [$startDate, $endDate])
                ->whereIn('apth.action', ['processing', 'submitted', 'paid'])
                ->orderBy('apth.action_date', 'desc')
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'title' => $payment->title,
                        'date' => Carbon::parse($payment->action_date)->format('Y-m-d'),
                        'amount' => (float) $payment->amount,
                        'status' => $this->translateStatus($payment->action),
                        'type' => 'seed',
                        'ma_trinh_thanh_toan' => $payment->ma_trinh_thanh_toan,
                        'vu_dau_tu' => $payment->vu_dau_tu,
                        'so_dot_thanh_toan' => $payment->so_dot_thanh_toan
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $seedPayments
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu thanh toán hom giống: ' . $e->getMessage()
            ], 500);
        }
    }

}