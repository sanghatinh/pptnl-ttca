
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Phiếu đề nghị thanh toán dịch vụ - TTC Attapeu</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
        }

        /* Print Layout */
        @page {
            size: A4 landscape;
            margin: 1.5cm;
        }

        /* Print button styles */
        .print-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }

        .print-btn {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            font-size: 12px;
        }

        .print-btn:hover {
            background: #0a58ca;
        }

        .orientation-selector {
            margin-bottom: 10px;
        }

        .orientation-selector label {
            margin-right: 15px;
            font-size: 12px;
        }

        .orientation-selector input {
            margin-right: 5px;
        }

        /* Landscape specific styles */
        @media print {
            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }
            
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .print-controls {
                display: none !important;
            }
            
            .section-header {
                background: #0d6efd !important;
                color: white !important;
            }
            
            table th {
                background-color: #f1f3f4 !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }

        /* Portrait specific styles */
      @media print and (orientation: portrait) {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            
            .container {
                max-width: 100%;
                font-size: 10px;
            }
            
            /* ปรับ header สำหรับแนวตั้ง - แถวเดียว */
            .header {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                padding-bottom: 10px;
            }
            
            .company-info {
                flex: 0 0 20%;
                text-align: left;
                margin-bottom: 0;
            }
            
            .company-info img {
                width: 30px;
                height: 30px;
                margin-bottom: 3px;
            }
            
            .company-info h3 {
                font-size: 8px;
                margin: 2px 0;
                line-height: 1.1;
            }
            
            .company-info p {
                font-size: 6px;
                margin: 1px 0;
                line-height: 1.1;
            }
            
            .title {
                flex: 0 0 80%;
                text-align: center;
                padding-left: 10px;
            }
            
            .title h1 {
                font-size: 12px;
                margin: 0 0 5px 0;
                line-height: 1.2;
            }
            
            .title p {
                font-size: 8px;
                margin: 2px 0;
                line-height: 1.1;
            }
            
            /* ปรับ info grid */
            .info-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .document-info {
                padding: 8px;
                margin-bottom: 12px;
            }
            
            .info-section h3 {
                font-size: 10px;
                margin-bottom: 6px;
            }
            
            .info-row {
                margin-bottom: 4px;
                flex-direction: row;
                align-items: flex-start;
            }
            
            .info-label {
                min-width: 80px;
                font-size: 8px;
                flex: 0 0 auto;
            }
            
            .info-value {
                font-size: 8px;
                flex: 1;
                padding-left: 5px;
            }
            
            /* ปรับตาราง สำหรับแนวตั้ง */
            table {
                font-size: 7px;
                margin-bottom: 15px;
            }
            
            table th {
                padding: 4px 2px;
                font-size: 6px;
                line-height: 1.2;
                word-wrap: break-word;
                max-width: 60px;
            }
            
            table td {
                padding: 3px 2px;
                font-size: 6px;
                line-height: 1.1;
                word-wrap: break-word;
                max-width: 60px;
                overflow: hidden;
            }
            
            /* ซ่อนคอลัมน์บางอันในแนวตั้ง */
            .hide-on-portrait {
                display: none !important;
            }
            
            /* ปรับขนาดคอลัมน์สำคัญ */
            table th:nth-child(1), table td:nth-child(1) { width: 5%; } /* STT */
            table th:nth-child(2), table td:nth-child(2) { width: 12%; } /* Mã nghiệm thu */
            table th:nth-child(3), table td:nth-child(3) { width: 8%; } /* Trạm */
            table th:nth-child(10), table td:nth-child(10) { width: 12%; } /* Tổng tiền */
            table th:nth-child(11), table td:nth-child(11) { width: 12%; } /* Tiền tạm giữ */
            table th:nth-child(12), table td:nth-child(12) { width: 12%; } /* Tiền thanh toán */
            
            /* Section headers */
            .section-header {
                font-size: 10px;
                padding: 6px 12px;
                margin: 12px 0 8px 0;
            }
            
            /* Status badge */
            .status-badge {
                font-size: 7px;
                padding: 2px 4px;
            }
            
            /* Amount styling */
            .amount {
                font-weight: bold;
            }
            
            /* Footer */
            .mt-3 {
                font-size: 7px;
                margin-top: 8px;
                padding-top: 8px;
            }
        }

        .container {
            width: 100%;
            max-width: 100%;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
        }

        .company-info {
            flex: 0 0 25%;
            text-align: center;
        }

        .company-info img {
            width: 60px;
            height: 60px;
            margin-bottom: 5px;
        }

        .company-info h3 {
            margin: 5px 0;
            font-size: 11px;
            color: #333;
            font-weight: bold;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 9px;
            color: #666;
        }

        .title {
            flex: 0 0 75%;
            text-align: center;
            align-self: center;
        }

        .title h1 {
            margin: 0;
            font-size: 18px;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
            font-style: italic;
        }

        /* Document Info Section */
        .document-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #0d6efd;
            border-bottom: 1px solid #0d6efd;
            padding-bottom: 5px;
            font-weight: bold;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }

        .info-label {
            font-weight: bold;
            min-width: 140px;
            color: #495057;
        }

        .info-value {
            color: #212529;
            flex: 1;
        }

        /* Section Headers */
        .section-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 12px 20px;
            margin: 25px 0 15px 0;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-header i {
            margin-right: 8px;
        }

        /* Table Styles */
        .table-container {
            margin-bottom: 25px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        table th {
            background-color: #f1f3f4;
            color: #333;
            font-weight: bold;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: middle;
            font-size: 10px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #e3f2fd;
        }

        /* Number formatting */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount {
            font-weight: bold;
            color: #28a745;
        }

        /* Footer */
        .table tfoot td {
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
            border-top: 2px solid #6c757d;
        }

        /* Empty State */
        .empty-section {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            font-style: italic;
        }

        .empty-section i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .company-info {
                margin-bottom: 15px;
            }
        }

        /* Custom spacing */
        .mb-3 {
            margin-bottom: 15px;
        }

        .mt-3 {
            margin-top: 15px;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-processing {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-submitted {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #99d6ff;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls">
        <div class="orientation-selector">
            <label>
                <input type="radio" name="orientation" value="landscape" checked>
                Khổ ngang (Landscape)
            </label>
            <label>
                <input type="radio" name="orientation" value="portrait">
                Khổ dọc (Portrait)
            </label>
        </div>
        <button class="print-btn" onclick="printReport()">🖨️ Print</button>
        <button class="print-btn" onclick="showPrintPreview()">👁️ Xem trước</button>
    </div>

    <div class="container">
        <!-- ...existing code... -->
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <img src="https://img5.pic.in.th/file/secure-sv1/TTC-LOGO-1.png" alt="TTC LOGO">
                <h3>TTC ATTAPEU SUGAR CANE SOLE CO.,LTD</h3>
                <p>Khamvongsa Village, Phouvong District,</p>
                <p>Attapeu Province, Laos</p>
            </div>
            <div class="title">
                <h1>BÁO CÁO PHIẾU ĐỀ NGHỊ THANH TOÁN DỊCH VỤ</h1>
                <p>Mã giải ngân: {{ $reportData['document_info']['ma_giai_ngan'] ?? 'N/A' }}</p>
                <p>Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Document Information -->
        <div class="document-info">
            <div class="info-grid">
                <!-- Left Column - Document Info -->
                <div class="info-section">
                    <h3><i>📄</i> Thông tin phiếu đề nghị</h3>
                    <div class="info-row">
                        <span class="info-label">Mã giải ngân:</span>
                        <span class="info-value">{{ $reportData['document_info']['ma_giai_ngan'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Vụ đầu tư:</span>
                        <span class="info-value">{{ $reportData['document_info']['vu_dau_tu'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Loại thanh toán:</span>
                        <span class="info-value">{{ $reportData['document_info']['loai_thanh_toan'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Trạng thái:</span>
                        <span class="info-value">
                            @php
                                $status = $reportData['document_info']['trang_thai_thanh_toan'] ?? 'processing';
                                $statusClass = 'status-' . $status;
                                $statusText = [
                                    'processing' => 'Đang xử lý',
                                    'submitted' => 'Đã nộp kế toán', 
                                    'paid' => 'Đã thanh toán',
                                    'rejected' => 'Từ chối'
                                ][$status] ?? 'Đang xử lý';
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">KH cá nhân:</span>
                        <span class="info-value">{{ $reportData['document_info']['khach_hang_ca_nhan'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">KH doanh nghiệp:</span>
                        <span class="info-value">{{ $reportData['document_info']['khach_hang_doanh_nghiep'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số tờ trình:</span>
                        <span class="info-value">{{ $reportData['document_info']['so_to_trinh'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số đợt thanh toán:</span>
                        <span class="info-value">{{ $reportData['document_info']['so_dot_thanh_toan'] ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Right Column - Financial Info -->
                <div class="info-section">
                    <h3><i>💰</i> Thông tin tài chính</h3>
                    <div class="info-row">
                        <span class="info-label">Tổng tiền:</span>
                        <span class="info-value amount">{{ number_format($reportData['financial_info']['tong_tien'] ?? 0, 0, ',', '.') }} KIP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tổng tiền tạm giữ:</span>
                        <span class="info-value">{{ number_format($reportData['financial_info']['tong_tien_tam_giu'] ?? 0, 0, ',', '.') }} KIP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tổng tiền khấu trừ:</span>
                        <span class="info-value">{{ number_format($reportData['financial_info']['tong_tien_khau_tru'] ?? 0, 0, ',', '.') }} KIP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tổng tiền lãi suất:</span>
                        <span class="info-value">{{ number_format($reportData['financial_info']['tong_tien_lai_suat'] ?? 0, 0, ',', '.') }} KIP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tổng tiền thanh toán còn lại:</span>
                        <span class="info-value amount">{{ number_format($reportData['financial_info']['tong_tien_thanh_toan_con_lai'] ?? 0, 0, ',', '.') }} KIP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày thanh toán:</span>
                        <span class="info-value">
                            @if($reportData['financial_info']['ngay_thanh_toan'])
                                {{ \Carbon\Carbon::parse($reportData['financial_info']['ngay_thanh_toan'])->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Sections Content -->
        @if(in_array('bienbannghiemthu', $reportData['selected_sections']))
            <div class="section-header">
                <i>📋</i> Biên bản nghiệm thu dịch vụ
            </div>
            
            @if(isset($reportData['sections_data']['bienbannghiemthu']) && count($reportData['sections_data']['bienbannghiemthu']) > 0)
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã nghiệm thu</th>
                                <th>Trạm</th>
                                <th>Vụ đầu tư</th>
                                <th>KH cá nhân</th>
                                <th>KH doanh nghiệp</th>
                                <th>Hợp đồng đầu tư</th>
                                <th>Hình thức thực hiện</th>
                                <th>Hợp đồng cung ứng</th>
                                <th>Tổng tiền (KIP)</th>
                                <th>Tiền tạm giữ (KIP)</th>
                                <th>Tiền thanh toán (KIP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalAmount = 0; $totalHold = 0; $totalPayment = 0; @endphp
                            @foreach($reportData['sections_data']['bienbannghiemthu'] as $index => $item)
                                @php
                                    $totalAmount += $item->tong_tien ?? 0;
                                    $totalHold += $item->tong_tien_tam_giu ?? 0;
                                    $totalPayment += $item->tong_tien_thanh_toan ?? 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->ma_nghiem_thu ?? 'N/A' }}</td>
                                    <td>{{ $item->tram ?? 'N/A' }}</td>
                                    <td>{{ $item->vu_dau_tu ?? 'N/A' }}</td>
                                    <td>{{ $item->khach_hang_ca_nhan_dt_mia ?? 'N/A' }}</td>
                                    <td>{{ $item->khach_hang_doanh_nghiep_dt_mia ?? 'N/A' }}</td>
                                    <td>{{ $item->hop_dong_dau_tu_mia ?? 'N/A' }}</td>
                                    <td>{{ $item->hinh_thuc_thuc_hien_dv ?? 'N/A' }}</td>
                                    <td>{{ $item->hop_dong_cung_ung_dich_vu ?? 'N/A' }}</td>
                                    <td class="text-right amount">{{ number_format($item->tong_tien ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->tong_tien_tam_giu ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right amount">{{ number_format($item->tong_tien_thanh_toan ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-center"><strong>TỔNG CỘNG</strong></td>
                                <td class="text-right amount"><strong>{{ number_format($totalAmount, 0, ',', '.') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalHold, 0, ',', '.') }}</strong></td>
                                <td class="text-right amount"><strong>{{ number_format($totalPayment, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="empty-section">
                    <i>📄</i>
                    <p>Không có dữ liệu biên bản nghiệm thu dịch vụ</p>
                </div>
            @endif
        @endif

        @if(in_array('chitietdichvu', $reportData['selected_sections']))
            <div class="section-header">
                <i>📝</i> Chi tiết nghiệm thu dịch vụ
            </div>
            
            @if(isset($reportData['sections_data']['chitietdichvu']) && count($reportData['sections_data']['chitietdichvu']) > 0)
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã nghiệm thu</th>
                                <th>Trạm</th>
                                <th>Dịch vụ</th>
                                <th>Mã số thửa</th>
                                <th>Đơn vị tính</th>
                                <th>Số lần thực hiện</th>
                                <th>Khối lượng thực hiện</th>
                                <th>Đơn giá (KIP)</th>
                                <th>Thành tiền (KIP)</th>
                                <th>Tiền còn lại (KIP)</th>
                                <th>Tiền thanh toán (KIP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalAmount = 0; $totalRemaining = 0; $totalPayment = 0; @endphp
                            @foreach($reportData['sections_data']['chitietdichvu'] as $index => $item)
                                @php
                                    $totalAmount += $item->thanh_tien ?? 0;
                                    $totalRemaining += $item->tien_con_lai ?? 0;
                                    $totalPayment += $item->tien_thanh_toan ?? 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->ma_nghiem_thu ?? 'N/A' }}</td>
                                    <td>{{ $item->tram ?? 'N/A' }}</td>
                                    <td>{{ $item->dich_vu ?? 'N/A' }}</td>
                                    <td>{{ $item->ma_so_thua ?? 'N/A' }}</td>
                                    <td>{{ $item->don_vi_tinh ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $item->so_lan_thuc_hien ?? 0 }}</td>
                                    <td class="text-right">{{ number_format($item->khoi_luong_thuc_hien ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->don_gia ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right amount">{{ number_format($item->thanh_tien ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->tien_con_lai ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right amount">{{ number_format($item->tien_thanh_toan ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-center"><strong>TỔNG CỘNG</strong></td>
                                <td class="text-right amount"><strong>{{ number_format($totalAmount, 0, ',', '.') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalRemaining, 0, ',', '.') }}</strong></td>
                                <td class="text-right amount"><strong>{{ number_format($totalPayment, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="empty-section">
                    <i>📝</i>
                    <p>Không có dữ liệu chi tiết nghiệm thu dịch vụ</p>
                </div>
            @endif
        @endif

        @if(in_array('phieuthuno', $reportData['selected_sections']))
            <div class="section-header">
                <i>🧾</i> Phiếu thu cấn trừ nợ
            </div>
            
            @if(isset($reportData['sections_data']['phieuthuno']) && count($reportData['sections_data']['phieuthuno']) > 0)
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã số phiếu</th>
                                <th>Invoice Number</th>
                                <th>Đã trả gốc (KIP)</th>
                                <th>Ngày vay</th>
                                <th>Ngày trả</th>
                                <th>Lãi suất (%)</th>
                                <th>Tiền lãi (KIP)</th>
                                <th>Vụ đầu tư</th>
                                <th>Vụ thanh toán</th>
                                <th>KH cá nhân</th>
                                <th>KH doanh nghiệp</th>
                                <th>Số tờ trình</th>
                                <th>Loại nợ</th>
                                <th>Mô tả</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalDeduction = 0; $totalInterest = 0; @endphp
                            @foreach($reportData['sections_data']['phieuthuno'] as $index => $item)
                                @php
                                    $totalDeduction += $item->da_tra_goc ?? 0;
                                    $totalInterest += $item->tien_lai ?? 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->ma_so_phieu ?? 'N/A' }}</td>
                                    <td>{{ $item->invoice_number ?? 'N/A' }}</td>
                                    <td class="text-right amount">{{ number_format($item->da_tra_goc ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($item->ngay_vay)
                                            {{ \Carbon\Carbon::parse($item->ngay_vay)->format('d/m/Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->ngay_tra)
                                            {{ \Carbon\Carbon::parse($item->ngay_tra)->format('d/m/Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($item->lai_suat ?? 0, 2, ',', '.') }}%</td>
                                    <td class="text-right">{{ number_format($item->tien_lai ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $item->vu_dau_tu ?? 'N/A' }}</td>
                                    <td>{{ $item->vu_thanh_toan ?? 'N/A' }}</td>
                                    <td>{{ $item->khach_hang_ca_nhan ?? 'N/A' }}</td>
                                    <td>{{ $item->khach_hang_doanh_nghiep ?? 'N/A' }}</td>
                                    <td>{{ $item->so_tro_trinh ?? 'N/A' }}</td>
                                    <td>{{ $item->category_debt ?? 'N/A' }}</td>
                                    <td>{{ $item->description ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-center"><strong>TỔNG CỘNG</strong></td>
                                <td class="text-right amount"><strong>{{ number_format($totalDeduction, 0, ',', '.') }}</strong></td>
                                <td colspan="3"></td>
                                <td class="text-right"><strong>{{ number_format($totalInterest, 0, ',', '.') }}</strong></td>
                                <td colspan="7"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="empty-section">
                    <i>🧾</i>
                    <p>Không có dữ liệu phiếu thu cấn trừ nợ</p>
                </div>
            @endif
        @endif

        <!-- Footer -->
        <div class="mt-3" style="border-top: 1px solid #dee2e6; padding-top: 15px; font-size: 10px; color: #6c757d;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Nội dung đã chọn:</strong>
                    @php
                        $sectionNames = [
                            'bienbannghiemthu' => 'Biên bản nghiệm thu dịch vụ',
                            'chitietdichvu' => 'Chi tiết nghiệm thu dịch vụ', 
                            'phieuthuno' => 'Phiếu thu cấn trừ nợ'
                        ];
                        $selectedNames = array_map(function($section) use ($sectionNames) {
                            return $sectionNames[$section] ?? $section;
                        }, $reportData['selected_sections']);
                    @endphp
                    {{ implode(', ', $selectedNames) }}
                </div>
                <div>
                    <strong>Trang:</strong> <span style="font-family: monospace;">1</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setOrientation(orientation) {
            const styleId = 'dynamic-page-style';
            let existingStyle = document.getElementById(styleId);
            
            if (existingStyle) {
                existingStyle.remove();
            }
            
            const style = document.createElement('style');
            style.id = styleId;
            
            if (orientation === 'portrait') {
                style.textContent = `
                    @page {
                        size: A4 portrait;
                        margin: 2cm;
                    }
                    @media print {
                        .container { font-size: 11px; }
                        table { font-size: 9px; }
                        table th, table td { padding: 6px 4px; }
                        .info-grid { grid-template-columns: 1fr; gap: 10px; }
                        .title h1 { font-size: 16px; }
                    }
                `;
            } else {
                style.textContent = `
                    @page {
                        size: A4 landscape;
                        margin: 1.5cm;
                    }
                `;
            }
            
            document.head.appendChild(style);
        }

        function printReport() {
            const selectedOrientation = document.querySelector('input[name="orientation"]:checked').value;
            setOrientation(selectedOrientation);
            
            // Small delay to ensure styles are applied
            setTimeout(() => {
                window.print();
            }, 100);
        }

        function showPrintPreview() {
            const selectedOrientation = document.querySelector('input[name="orientation"]:checked').value;
            setOrientation(selectedOrientation);
            
            // Trigger print preview
            setTimeout(() => {
                window.print();
            }, 100);
        }

        // Handle orientation change
        document.querySelectorAll('input[name="orientation"]').forEach(radio => {
            radio.addEventListener('change', function() {
                setOrientation(this.value);
            });
        });

        // Initialize with default orientation
        document.addEventListener('DOMContentLoaded', function() {
            setOrientation('landscape');
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printReport();
            }
        });
    </script>
</body>
</html>