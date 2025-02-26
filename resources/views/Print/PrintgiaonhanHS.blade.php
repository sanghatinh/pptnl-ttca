
<html>
  <head>
    <title>Print Report</title>
    <meta charset="UTF-8" />

    <style>
      body {
        margin: 0;
        padding: 0;
        width: 297mm;
        min-height: 210mm;
        background: white;
        /* border: 1px solid red; */
      }

      .container {
  width: 277mm; /* 297mm - 20mm margins */
  margin: 5mm auto;
  padding: 5mm;
  background: white;
  box-sizing: border-box;
}

      .header {
        margin-top: 0px;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .header-left {
        text-align: left;
        position: relative;
      }
      .header-right {
        text-align: right;
      }
      .header-right p {
        margin-right: 30px;
      }
      .header img {
        margin-left: 120px;
        width: 100px;
      }
      .title {
        text-align: center;
        margin: 20px 0;
      }
      .info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
      }
      .info p {
        margin: 5px 0;
      }
      .info span {
        margin: 5px 0;
        font-weight: bold;
      }
      .table-container {
        overflow-x: auto;
      }

      .container-footer {
        display: flex;
        justify-content: space-around;
        margin-top: 10px;
      }
      .item {
        text-align: center;
        font-weight: bold;
      }
      .item p {
        margin: 0;
        font-size: 20px;
      }
      .item span {
        display: block;
        margin-top: 10px;
        font-size: 14px;
        font-weight: bold;
      }

      .footer-text {
        display: flex;
        gap: 2rem;
        font-size: 16px;
        font-weight: bold;
        justify-content: center;
      }
      /* Add these styles inside the existing <style> tag */
.table-container {
    margin: 20px 0;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
    background-color: transparent;
    font-size: 12px;
}

.table th,
.table td {
 
    border: 1px solid #dee2e6;
    text-align: left;
    padding: 8px;
    white-space: nowrap;
    overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #333;
}

.table tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.05);
}

.table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}



/* Modify the QR-code class */
.QR-code {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 10px 0;
}

.QR-code img {
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <div class="header-left">
          <img
            src="https://img5.pic.in.th/file/secure-sv1/TTC-LOGOebfc0447a292a4df.png"
            alt="Company Logo"
            style="width: 100px; height: auto"
            onload="this.onload=null; this.src='https://img5.pic.in.th/file/secure-sv1/TTC-LOGOebfc0447a292a4df.png';"
          />
          <p>TTC ATTAPEU SUGAR CANE SOLE CO.,LTD</p>
          <p>Khamvongsa Village, Phouvong District, Attapeu Province, Laos</p>
        </div>
        <div class="header-right">
          <p class="text-lao">CỘNG HÒA DÂN CHỦ NHÂN DÂN LÀO</p>
          <p>Hòa bình - Độc lập - Hòa hữu - Thống nhất - Thịnh vượng</p>
          <div class="QR-code">
            <img
              src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=HelloWorld"
              alt="QR Code"
              style="width: 80px; height: 80px; border: 1px solid black"
            />
          </div>
        </div>
      </div>

      <div class="title">
        <h4></h4>
        <h4>BẢNG GIAO NHẬN HỒ SƠ TỪ TRẠM GIAO P.PTNL VỤ ĐT 24/25</h4>
      </div>

      <div class="info">
    <div class="info-left">
        <p>Mã số phiếu : <span>{{ $document->document_code }}</span> </p>
        <p>Ngày lập : {{ date('d/m/Y', strtotime($document->created_date)) }}</p>
        <p>Người lập : <span>{{ $document->creator->full_name }}</span> </p>
        <p>Số lượng hồ sơ: {{ count($nghiemThuDocuments) + count($homGiongDocuments) }}</p>
        <p>Trạm: {{ $document->creator->station }}</p>
    </div>
    <div class="info-right">
    @if (!in_array($document->status, ['creating', 'sending']))
        <p>Người nhận: {{ $document->receiver_info['name'] ?? 'Chưa có người nhận' }}</p>
        <p>Ngày nhận: {{ $document->receiver_info['date'] ? date('d/m/Y', strtotime($document->receiver_info['date'])) : 'Chưa nhận' }}</p>
    @endif
</div>
</div>

<h3>I.BIÊN BẢN NGHIỆM THU DỊCH VỤ :</h3>
<hr>
<div class="table-container">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã nghiệm thu</th>
                <th>Trạm</th>
               
                <th>Vụ đầu tư</th>
               
                <th>Hợp đồng đầu tư mía</th>
                <th>Hình thức thực hiện DV</th>
                <th>Hợp đồng cung ứng dịch vụ</th>
                <th>Tổng tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nghiemThuDocuments as $index => $doc)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $doc->bienBanNghiemThu->ma_nghiem_thu }}</td>
                <td>{{ $doc->bienBanNghiemThu->tram }}</td>
              
                <td>{{ $doc->bienBanNghiemThu->vu_dau_tu }}</td>
              
                <td>{{ $doc->bienBanNghiemThu->hop_dong_dau_tu_mia }}</td>
                <td>{{ $doc->bienBanNghiemThu->hinh_thuc_thuc_hien_dv }}</td>
                <td>{{ $doc->bienBanNghiemThu->hop_dong_cung_ung_dich_vu }}</td>
                <td>{{ number_format($doc->bienBanNghiemThu->tong_tien, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<h3>II.BIÊN BẢN HOM GIỐNG</h3>
<hr>
<div class="table-container">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã số phiếu</th>
             
                <th>Vụ đầu tư</th>
                
                <th>Hợp đồng đầu tư mía bên giao</th>
                <th>Hợp đồng đầu tư mía bên nhận</th>
                <th>Tổng thực nhận</th>
                <th>Tổng tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($homGiongDocuments as $index => $doc)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $doc->bienBanHomGiong->ma_so_phieu }}</td>
               
                <td>{{ $doc->bienBanHomGiong->vu_dau_tu }}</td>
              
                <td>{{ $doc->bienBanHomGiong->hop_dong_dau_tu_mia_ben_giao_hom }}</td>
                <td>{{ $doc->bienBanHomGiong->hop_dong_dau_tu_mia }}</td>
                <td>{{ $doc->bienBanHomGiong->tong_thuc_nhan }}</td>
                <td>{{ number_format($doc->bienBanHomGiong->tong_tien, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<hr>
<div class="container-footer">
        <div class="item">
          <span>BÊN GIAO</span>
        </div>
        <div class="item">
          <span>BÊN NHẬN</span>
        </div>
      </div>

 
    </div>
  </body>
</html>
