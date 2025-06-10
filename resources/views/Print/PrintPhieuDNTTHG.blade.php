
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Đề Nghị Thanh Toán</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            background: white;
        }

        .container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
        }

        .header {
            display: flex;
            width: 100%;
            margin-bottom: 20px;
            border: 2px solid #000;
        }

        .header-left {
            width: 25%;
            text-align: center;
            border-right: 2px solid #000;
            padding: 15px 10px;
            font-weight: bold;
            font-size: 12px;
            background-color: #f8f8f8;
        }

        .header-center {
            width: 50%;
            text-align: center;
            border-right: 2px solid #000;
            padding: 15px 20px;
        }

        .header-center h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .header-right {
            width: 25%;
            padding: 10px;
            font-size: 10px;
            line-height: 1.4;
            background-color: #f8f8f8;
        }

        .form-section {
            margin-bottom: 15px;
            margin-top: 20px;
        }

        .form-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }

        .form-row label {
            font-weight: bold;
            margin-right: 10px;
            min-width: 140px;
        }

        .form-row input, .form-row select {
            border: none;
            border-bottom: 1px solid #000;
            padding: 2px 5px;
            background: transparent;
            flex: 1;
            margin-right: 15px;
        }

        .checkbox-section {
            background-color: #f0f0f0;
            padding: 8px;
            margin: 15px 0;
            border: 1px solid #000;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .checkbox-row input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .table-container {
            margin: 20px 0;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 11px;
        }

        .payment-table th, .payment-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            vertical-align: middle;
        }

        .payment-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .payment-table .text-left {
            text-align: left;
        }

        .payment-table .amount-cell {
            text-align: right;
            font-weight: bold;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-box {
            text-align: center;
            width: 23%;
        }

        .signature-box .title {
            font-weight: bold;
            margin-bottom: 120px;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }

        .signature-box .name {
            font-weight: bold;
            margin-top: 10px;
        }

        .amount-highlight {
            background-color: #ffff99;
            font-weight: bold;
        }

        .page-number {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
        }

        .form-row-multiline textarea {
            width: 100%;
            border: none;
            border-bottom: 1px solid #000;
            padding: 2px 5px;
            background: transparent;
            resize: none;
            overflow: hidden;
            min-height: 20px;
            font-family: 'Times New Roman', serif;
            font-size: 14px;
        }

        @media print {
            .container {
                margin: 0;
                padding: 0.5mm;
            }
            
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @foreach($printData as $index => $data)
    @if($index > 0)
    <div style="page-break-before: always;"></div>
    @endif
    
    <div class="container">
        <!-- Header with 3 sections -->
        <div class="header">
            <div class="header-left">
                TTC ATTAPEU
            </div>
            <div class="header-center">
                <h1>PHIẾU ĐỀ NGHỊ THANH TOÁN</h1>
            </div>
            <div class="header-right">
                Mã số: TGT/GT-Gd-M02<br>
                Ban hành: 02<br>
                Hiệu lực: 1/9/2018
            </div>
        </div>

        <!-- Form Content -->
        <div class="form-section">
            <div class="form-row">
                <label>Mã giải ngân:</label> 
                <input type="text" value="{{ $data['ma_giai_ngan'] }}" style="font-weight: bold;">
            </div>
            <div class="form-row">
                <label>Người đề nghị thanh toán:</label>
                <input type="text" value="Sang Sengtongdeng">
                <label style="margin-left: 20px;">Ngày:</label>
                <input type="text" value="{{ $data['ngay_print'] }}" style="width: 100px;">
            </div>
            
            <div class="form-row">
                <label>Đơn vị công tác:</label>
                <input type="text" value="P.PTNL">
            </div>
            
            <div class="form-row" style="flex-direction: row; align-items: flex-start;">
                <label style="margin-bottom: 5px;">Nội dung thanh toán:</label>
                <div style="width: 100%; border-bottom: 1px solid #000; padding: 2px 5px; font-size: 14px; line-height: 1.4; word-wrap: break-word;">
                    {{ $data['noi_dung_thanh_toan'] }}
                </div>
            </div>
            
            <div class="form-row">
                <label>Cho Đơn vị dịch vụ:</label>
                <input type="text" value="{{ $data['don_vi_dich_vu'] }}">
            </div>
            
            <div class="form-row">
                <label>Hình thức thanh toán:</label>
                <span style="display: inline-flex; align-items: center; margin-right: 20px;">
                    <input type="checkbox" style="margin-right: 5px;">
                    <span>Tiền mặt</span>
                </span>
                <span style="display: inline-flex; align-items: center;">
                    <input type="checkbox" checked style="margin-right: 5px;">
                    <span>Chuyển khoản</span>
                </span>
            </div>
            
            <div class="form-row">
                <label>Số tiền đề nghị thanh toán:</label>
                <input type="text" value="{{ number_format($data['so_tien_de_nghi']) }}" class="amount-highlight">
                <label>KIP</label>
                <span style="display: inline-flex; align-items: center; margin-left: 20px;">
                    <input type="checkbox" style="margin-right: 5px;">
                    <span>Hóa đơn bán lẻ</span>
                </span>
            </div>
            
            <div class="form-row">
                <label>Bằng chữ:</label>
                <input type="text" value="...">
            </div>
        </div>

        <!-- Checkbox Section -->
        <div class="checkbox-section">
            <div style="font-weight: bold; margin-bottom: 8px;">Phân định chi thanh toán bằng hình thức chuyển khoản/ủy quyền nhận tiền mặt:</div>
            <div class="checkbox-row">
                <span>Đơn vị/ cá nhân nhận tiền:</span>
                <span style="margin-left: 20px; font-weight: bold;">{{ $data['ca_nhan_nhan_tien'] }}</span>
            </div>
            <div class="checkbox-row">
                <span>Đơn vị công tác:</span>
                <span style="margin-left: 20px;">Số CMND/Passport:</span>
            </div>
            <div class="checkbox-row">
                <span>Số tài khoản:</span>
                <span style="margin-left: 20px; font-weight: bold;">{{ $data['so_tai_khoan'] }}</span>
                <span style="margin-left: 220px;"> tại NH: </span>
                <span style="margin-left: 20px; font-weight: bold;">{{ $data['ten_ngan_hang'] }}</span>
            </div>
            <div class="checkbox-row">
                <span>Thời hạn thanh toán:</span>
                <span style="margin-left: 20px;">Từ ngày: ..../..../202.....</span>
                <span style="margin-left: 20px;">- Hạn chót: ngày ..../..../202.....</span>
            </div>
        </div>

        <!-- Payment Table -->
        <div class="table-container">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th colspan="3">Chứng từ kèm theo gồm:</th>
                        <th rowspan="2">Đơn vị</th>
                        <th colspan="2">Hình thức</th>
                        <th rowspan="2">Số tiền (KIP)</th>
                    </tr>
                    <tr>
                        <th>Số CT</th>
                        <th>Loại chứng từ</th>
                        <th>Ngày</th>
                        <th>Gốc</th>
                        <th>Sao</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="text-left">Phiếu giao nhận hom giống</td>
                        <td></td>
                        <td></td>
                        <td>X</td>
                        <td></td>
                        <td class="amount-cell">{{ number_format($data['tong_tien']) }}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="text-left">{{ $data['so_to_trinh'] }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr><td>3</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    <tr><td>4</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    
                    <tr>
                        <td colspan="6" style="text-align: center; font-weight: bold;">Tổng cộng</td>
                        <td class="amount-cell amount-highlight">{{ number_format($data['tong_tien']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="title">Người đề nghị</div>
                <div class="name">Sang Sengtongdeng</div>
            </div>
            <div class="signature-box">
                <div class="title">Trưởng đơn vị</div>
                <div class="name">Phan Văn Tèo</div>
            </div>
            <div class="signature-box">
                <div class="title">Kế toán trưởng</div>
            </div>
            <div class="signature-box">
                <div class="title">Phê duyệt</div>
            </div>
        </div>

        <div class="page-number">{{ $index + 1 }}</div>
    </div>
    @endforeach
</body>
</html>