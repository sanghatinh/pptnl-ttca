<template lang="">
    <breadcrumb-vue />

    <!-- Original content -->
    <div class="card shadow">
        <div class="card-body p-0">
            <PerfectScrollbar
                :options="{
                    wheelSpeed: 1,
                    wheelPropagation: true,
                    minScrollbarLength: 20,
                }"
                class="scroll-area"
            >
                <!-- Main content with added top margin -->
                <div class="main-content-wrapper">
                    <div class="d-flex flex-column flex-md-row gap-1">
                        <!-- Thông tin phiếu đề nghị -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title mb-3 border-bottom pb-2">
                                    Thông tin phiếu đề nghị
                                </h5>
                                <div class="row gutters">
                                    <!-- Mã đề nghị giải ngân -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maGiaiNgan"
                                                class="form-label"
                                            >
                                                Mã đề nghị giải ngân
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maGiaiNgan"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Vụ đầu tư -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="vuDauTu"
                                                class="form-label"
                                            >
                                                Vụ đầu tư
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="vuDauTu"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Loại thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="loaiThanhToan"
                                                class="form-label"
                                            >
                                                Loại thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="loaiThanhToan"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Trạng thái thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="trangThai"
                                                class="form-label"
                                            >
                                                Trạng thái thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="trangThai"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Khách hàng cá nhân -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="khachHangCaNhan"
                                                class="form-label"
                                            >
                                                Khách hàng cá nhân
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="khachHangCaNhan"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Mã khách hàng cá nhân -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maKhachHangCaNhan"
                                                class="form-label"
                                            >
                                                Mã khách hàng cá nhân
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maKhachHangCaNhan"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Khách hàng doanh nghiệp -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="khachHangDoanhNghiep"
                                                class="form-label"
                                            >
                                                Khách hàng doanh nghiệp
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="khachHangDoanhNghiep"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Mã khách hàng doanh nghiệp -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maKhachHangDoanhNghiep"
                                                class="form-label"
                                            >
                                                Mã khách hàng doanh nghiệp
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maKhachHangDoanhNghiep"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Số tờ trình -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="soToTrinh"
                                                class="form-label"
                                            >
                                                Số tờ trình
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="soToTrinh"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Số đợt thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="soDotThanhToan"
                                                class="form-label"
                                            >
                                                Số đợt thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="soDotThanhToan"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin tài chính -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title mb-3 border-bottom pb-2">
                                    Thông tin tài chính
                                </h5>
                                <div class="row gutters">
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTien"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTien"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienTamGiu"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền tạm giữ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienTamGiu"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienKhauTru"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền khấu trừ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienKhauTru"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienLaiSuat"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền lãi suất
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienLaiSuat"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12"
                                    >
                                        <div class="form-group mb-0">
                                            <label
                                                for="tongTienConLai"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền thanh toán còn lại
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienConLai"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Ngày thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="ngayThanhToan"
                                                class="form-label"
                                            >
                                                Ngày thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="ngayThanhToan"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Hồ sơ đính kèm -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="hoSoDinhKem"
                                                class="form-label"
                                            >
                                                Hồ sơ đính kèm
                                            </label>
                                            <div
                                                class="d-flex align-items-center"
                                            >
                                                <a
                                                    href="#"
                                                    @click.prevent="
                                                        openAttachment
                                                    "
                                                    class="text-decoration-none"
                                                >
                                                    <div
                                                        class="d-flex align-items-center"
                                                    >
                                                        <i
                                                            class="far fa-file-pdf text-danger fs-4 me-2"
                                                        ></i>
                                                        <span
                                                            >Xem hồ sơ đính
                                                            kèm</span
                                                        >
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Ghi chú -->
                                    <div
                                        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="ghiChu"
                                                class="form-label"
                                            >
                                                Ghi chú
                                            </label>
                                            <div class="note-container">
                                                <div
                                                    class="note-content"
                                                    id="ghiChu"
                                                >
                                                    <p
                                                        v-if="document.ghi_chu"
                                                        class="mb-0"
                                                    >
                                                        {{ document.ghi_chu }}
                                                    </p>
                                                    <p
                                                        v-else
                                                        class="text-muted fst-italic mb-0"
                                                    >
                                                        Không có ghi chú
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Nghiệm thu dịch vụ -->
                    <!-- First, update the table in the template to loop through the bienbans data -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span class="toggle-section cursor-pointer">
                                    <i
                                        class="fas fa-angle-down me-2 toggle-icon"
                                    ></i>
                                    Biên bản nghiệm thu dịch vụ
                                </span>
                            </h5>
                        </div>
                        <div class="card-body" style="display: none">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã nghiệm thu</th>
                                                <th>Trạm</th>
                                                <th>Vụ đầu tư</th>
                                                <th>
                                                    Khách hàng cá nhân ĐT mía
                                                </th>
                                                <th>
                                                    Khách hàng doanh nghiệp ĐT
                                                    mía
                                                </th>
                                                <th>Hợp đồng đầu tư</th>
                                                <th>Hình thức DV</th>
                                                <th>Hợp đồng cung ứng DV</th>
                                                <th>Thành tiền</th>
                                                <th>Số tiền tạm giữ</th>
                                                <th>Tiền thanh toán</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="bienbans.length === 0">
                                                <td
                                                    colspan="12"
                                                    class="text-center py-4"
                                                >
                                                    <div class="empty-state">
                                                        <i
                                                            class="fas fa-file-invoice empty-icon"
                                                        ></i>
                                                        <p>
                                                            Chưa có dữ liệu chi
                                                            tiết
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="(
                                                    item, index
                                                ) in bienbans"
                                                :key="item.ma_nghiem_thu"
                                            >
                                                <td class="text-center">
                                                    {{ index + 1 }}
                                                </td>
                                                <td>
                                                    {{ item.ma_nghiem_thu }}
                                                </td>
                                                <td>{{ item.tram }}</td>
                                                <td>{{ item.vu_dau_tu }}</td>
                                                <td>
                                                    {{
                                                        item.khach_hang_ca_nhan_dt_mia
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_doanh_nghiep_dt_mia
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.hop_dong_dau_tu_mia
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.hinh_thuc_thuc_hien_dv
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.hop_dong_cung_ung_dich_vu
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tong_tien
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tong_tien_tam_giu
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tong_tien_thanh_toan
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td
                                                    colspan="9"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totals.total_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totals.total_hold_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totals.total_payment_amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết nghiệm thu dịch vụ -->
                    <!-- Chi tiết nghiệm thu dịch vụ -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span class="toggle-section cursor-pointer">
                                    <i
                                        class="fas fa-angle-down me-2 toggle-icon"
                                    ></i>
                                    Chi tiết nghiệm thu dịch vụ
                                </span>
                            </h5>
                        </div>
                        <div class="card-body" style="display: none">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã nghiệm thu</th>
                                                <th>Trạm</th>
                                                <th>Dịch vụ</th>
                                                <th>Mã số thửa</th>
                                                <th>Đơn vị tính</th>
                                                <th>Số lần thực hiện</th>
                                                <th>Khối lượng thực hiện</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                                <th>Số tiền tạm giữ</th>
                                                <th>Tiền thanh toán</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-if="
                                                    chitietDichVu.length === 0
                                                "
                                            >
                                                <td
                                                    colspan="12"
                                                    class="text-center py-4"
                                                >
                                                    <div class="empty-state">
                                                        <i
                                                            class="fas fa-file-invoice empty-icon"
                                                        ></i>
                                                        <p>
                                                            Chưa có dữ liệu chi
                                                            tiết
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="(
                                                    item, index
                                                ) in chitietDichVu"
                                                :key="index"
                                            >
                                                <td class="text-center">
                                                    {{ index + 1 }}
                                                </td>
                                                <td>
                                                    {{ item.ma_nghiem_thu }}
                                                </td>
                                                <td>{{ item.tram }}</td>
                                                <td>{{ item.dich_vu }}</td>
                                                <td>{{ item.ma_so_thua }}</td>
                                                <td>{{ item.don_vi_tinh }}</td>
                                                <td class="text-center">
                                                    {{ item.so_lan_thuc_hien }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatNumber(
                                                            item.khoi_luong_thuc_hien
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.don_gia
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.thanh_tien
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tien_tam_giu
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tien_thanh_toan
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td
                                                    colspan="9"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            chitietTotals.total_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            chitietTotals.total_hold_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            chitietTotals.total_payment_amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phiếu thu hồi nợ -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span class="toggle-section cursor-pointer">
                                    <i
                                        class="fas fa-angle-down me-2 toggle-icon"
                                    ></i>
                                    Phiếu thu cấn trừ nợ
                                </span>
                            </h5>
                        </div>
                        <div class="card-body" style="display: none">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã số phiếu</th>
                                                <th>Invoice Number</th>
                                                <th>Đã trả gốc</th>
                                                <th>Ngày vay</th>
                                                <th>Ngày trả</th>
                                                <th>Lãi suất</th>
                                                <th>Tiền lãi</th>
                                                <th>Vụ đầu tư</th>
                                                <th>Vụ thanh toán</th>
                                                <th>Khách hàng cá nhân</th>
                                                <th>KH doanh nghiệp</th>
                                                <th>Số trờ trình</th>
                                                <th>Category Debt</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="phieuThuNo.length === 0">
                                                <td
                                                    colspan="15"
                                                    class="text-center py-4"
                                                >
                                                    <div class="empty-state">
                                                        <i
                                                            class="fas fa-file-invoice empty-icon"
                                                        ></i>
                                                        <p>
                                                            Chưa có dữ liệu chi
                                                            tiết
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="(
                                                    item, index
                                                ) in phieuThuNo"
                                                :key="index"
                                            >
                                                <td class="text-center">
                                                    {{ index + 1 }}
                                                </td>
                                                <td>{{ item.ma_so_phieu }}</td>
                                                <td>
                                                    {{ item.invoice_number }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.da_tra_goc
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        formatDate(
                                                            item.ngay_vay
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        formatDate(
                                                            item.ngay_tra
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-center">
                                                    {{ item.lai_suat }}%
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tien_lai
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ item.vu_dau_tu }}</td>
                                                <td>
                                                    {{ item.vu_thanh_toan }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_ca_nhan
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_doanh_nghiep
                                                    }}
                                                </td>
                                                <td>{{ item.so_tro_trinh }}</td>
                                                <td>
                                                    {{ item.category_debt }}
                                                </td>
                                                <td>{{ item.description }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td
                                                    colspan="3"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            phieuThuNoTotals.total_deduction
                                                        )
                                                    }}
                                                </td>
                                                <td colspan="3"></td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            phieuThuNoTotals.total_interest
                                                        )
                                                    }}
                                                </td>
                                                <td colspan="7"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </PerfectScrollbar>
        </div>
    </div>
</template>
<script>
import { useStore } from "../../Store/Auth";
import axios from "axios";
import Swal from "sweetalert2";

export default {
    setup() {
        const store = useStore();
        return { store };
    },
    data() {
        return {
            document: {
                ma_giai_ngan: "",
                vu_dau_tu: "",
                loai_thanh_toan: "",
                trang_thai_thanh_toan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "",
                tong_tien: 0,
                tong_tien_tam_giu: 0,
                tong_tien_khau_tru: 0,
                tong_tien_lai_suat: 0,
                tong_tien_thanh_toan_con_lai: 0,
                ngay_thanh_toan: "",
                attachment_url: "", // Add this line
                so_to_trinh: "", // Add this line
                so_dot_thanh_toan: "", // Add this line
                ghi_chu: "",
            },
            bienbans: [], // Array to hold nghiệm thu dịch vụ data
            totals: {
                total_amount: 0,
                total_hold_amount: 0,
                total_payment_amount: 0,
            },
            chitietDichVu: [], // Array to hold chi tiết nghiệm thu dịch vụ data
            chitietTotals: {
                total_amount: 0,
                total_hold_amount: 0,
                total_payment_amount: 0,
            },
            phieuThuNo: [], // Array to hold phieu thu no dich vu data
            phieuThuNoTotals: {
                total_deduction: 0,
                total_interest: 0,
            },

            isLoading: false,
            showDetails: true,
        };
    },
    computed: {
        statusClass() {
            const status = this.document.trang_thai_thanh_toan;
            if (!status) return "";

            const classes = {
                submitted: "badge bg-primary",
                processing: "badge bg-warning",
                paid: "badge bg-success",
                rejected: "badge bg-danger",
            };

            return classes[status] || "badge bg-secondary";
        },
    },
    mounted() {
        this.fetchUserData();
        this.fetchDocument();
        this.fetchBienBanData();
        this.fetchChiTietDichVu();
        this.fetchPhieuThuNo();

        // Setup toggle functionality for sections
        document.querySelectorAll(".toggle-section").forEach((el) => {
            const icon = el.querySelector(".toggle-icon");
            const cardBody = el.closest(".card").querySelector(".card-body");

            // Ensure icon is right-pointing (collapsed state)
            if (icon.classList.contains("fa-angle-down")) {
                icon.classList.replace("fa-angle-down", "fa-angle-right");
            }

            // Hide the card body initially
            cardBody.style.display = "none";

            // Add click event listener for toggling
            el.addEventListener("click", () => {
                if (icon.classList.contains("fa-angle-right")) {
                    icon.classList.replace("fa-angle-right", "fa-angle-down");
                    cardBody.style.display = "block";
                } else {
                    icon.classList.replace("fa-angle-down", "fa-angle-right");
                    cardBody.style.display = "none";
                }
            });
        });
    },
    methods: {
        fetchPhieuThuNo() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            axios
                .get(`/api/payment-requests-dichvu/${id}/phieu-thu-no`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.phieuThuNo = response.data.data || [];
                        this.phieuThuNoTotals = response.data.totals || {
                            total_deduction: 0,
                            total_interest: 0,
                        };
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể tải dữ liệu phiếu thu nợ"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching phieu thu no data:", error);
                    this.showError("Lỗi khi tải dữ liệu phiếu thu nợ");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        fetchChiTietDichVu() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            axios
                .get(`/api/payment-requests-dichvu/${id}/chitiet-dichvu`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.chitietDichVu = response.data.data || [];
                        this.chitietTotals = response.data.totals || {
                            total_amount: 0,
                            total_hold_amount: 0,
                            total_payment_amount: 0,
                        };
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể tải dữ liệu chi tiết nghiệm thu"
                        );
                    }
                })
                .catch((error) => {
                    console.error(
                        "Error fetching chi tiết nghiệm thu data:",
                        error
                    );
                    this.showError("Lỗi khi tải dữ liệu chi tiết nghiệm thu");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        fetchBienBanData() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            axios
                .get(`/api/payment-requests-dichvu/${id}/bienban-nghiemthu`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.bienbans = response.data.data || [];
                        this.totals = response.data.totals || {
                            total_amount: 0,
                            total_hold_amount: 0,
                            total_payment_amount: 0,
                        };
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể tải dữ liệu nghiệm thu"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching nghiệm thu data:", error);
                    this.showError("Lỗi khi tải dữ liệu nghiệm thu");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                this.user = JSON.parse(user);
            }
        },
        fetchDocument() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            this.isLoading = true;
            axios
                .get(`/api/payment-requests-dichvu/${id}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.document = {
                            ma_giai_ngan:
                                response.data.document.ma_giai_ngan || "",
                            vu_dau_tu: response.data.document.vu_dau_tu || "",
                            loai_thanh_toan:
                                response.data.document.loai_thanh_toan || "",
                            trang_thai_thanh_toan:
                                response.data.document.trang_thai_thanh_toan ||
                                "",
                            khach_hang_ca_nhan:
                                response.data.document.khach_hang_ca_nhan || "",
                            ma_khach_hang_ca_nhan:
                                response.data.document.ma_khach_hang_ca_nhan ||
                                "",
                            khach_hang_doanh_nghiep:
                                response.data.document
                                    .khach_hang_doanh_nghiep || "",
                            ma_khach_hang_doanh_nghiep:
                                response.data.document
                                    .ma_khach_hang_doanh_nghiep || "",
                            tong_tien: response.data.document.tong_tien || 0,
                            tong_tien_tam_giu:
                                response.data.document.tong_tien_tam_giu || 0,
                            tong_tien_khau_tru:
                                response.data.document.tong_tien_khau_tru || 0,
                            tong_tien_lai_suat:
                                response.data.document.tong_tien_lai_suat || 0,
                            tong_tien_thanh_toan_con_lai:
                                response.data.document
                                    .tong_tien_thanh_toan_con_lai || 0,
                            ngay_thanh_toan:
                                response.data.document.ngay_thanh_toan || "",
                            attachment_url:
                                response.data.document.attachment_url || "",
                            so_to_trinh:
                                response.data.document.so_to_trinh || "",
                            so_dot_thanh_toan:
                                response.data.document.so_dot_thanh_toan || "",
                            ghi_chu: response.data.document.ghi_chu || "",
                        };

                        // Update form fields with document data
                        this.$nextTick(() => {
                            document.getElementById("maGiaiNgan").value =
                                this.document.ma_giai_ngan;
                            document.getElementById("vuDauTu").value =
                                this.document.vu_dau_tu;
                            document.getElementById("loaiThanhToan").value =
                                this.document.loai_thanh_toan;
                            document.getElementById("trangThai").value =
                                this.formatStatus(
                                    this.document.trang_thai_thanh_toan
                                );
                            document.getElementById("khachHangCaNhan").value =
                                this.document.khach_hang_ca_nhan;
                            document.getElementById("maKhachHangCaNhan").value =
                                this.document.ma_khach_hang_ca_nhan;
                            document.getElementById(
                                "khachHangDoanhNghiep"
                            ).value = this.document.khach_hang_doanh_nghiep;
                            document.getElementById(
                                "maKhachHangDoanhNghiep"
                            ).value = this.document.ma_khach_hang_doanh_nghiep;
                            document.getElementById("soToTrinh").value =
                                this.document.so_to_trinh;
                            document.getElementById("soDotThanhToan").value =
                                this.document.so_dot_thanh_toan;
                            document.getElementById("ngayThanhToan").value =
                                this.formatDate(this.document.ngay_thanh_toan);

                            document.getElementById("tongTien").value =
                                this.formatCurrency(this.document.tong_tien);
                            document.getElementById("tongTienTamGiu").value =
                                this.formatCurrency(
                                    this.document.tong_tien_tam_giu
                                );
                            document.getElementById("tongTienKhauTru").value =
                                this.formatCurrency(
                                    this.document.tong_tien_khau_tru
                                );
                            document.getElementById("tongTienLaiSuat").value =
                                this.formatCurrency(
                                    this.document.tong_tien_lai_suat
                                );
                            document.getElementById("tongTienConLai").value =
                                this.formatCurrency(
                                    this.document.tong_tien_thanh_toan_con_lai
                                );
                        });
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không tìm thấy thông tin phiếu đề nghị"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching document:", error);
                    this.showError("Lỗi khi tải thông tin phiếu đề nghị");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        formatCurrency(value) {
            if (!value) return "0 KIP";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "KIP",
                maximumFractionDigits: 0,
            }).format(value);
        },

        formatNumber(value) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN").format(value);
        },

        formatDate(date) {
            if (!date) return "N/A";
            try {
                return new Date(date).toLocaleDateString("vi-VN");
            } catch (e) {
                console.error("Error formatting date:", e);
                return "N/A";
            }
        },

        formatStatus(status) {
            const statusMap = {
                submitted: "Đã nộp kế toán",
                processing: "Đang xử lý",
                paid: "Đã thanh toán",
                rejected: "Từ chối",
            };
            return statusMap[status] || status || "N/A";
        },

        toggleDetails() {
            this.showDetails = !this.showDetails;
        },
        // Add this method

        handleAuthError() {
            // Handle authentication error
            this.showError("Phiên làm việc đã hết hạn, vui lòng đăng nhập lại");
            localStorage.removeItem("access_token");
            localStorage.removeItem("web_user");
            this.$router.push("/login");
        },
        openAttachment() {
            // This function will open the attachment URL
            const attachmentUrl = this.document.attachment_url || "#";

            if (attachmentUrl && attachmentUrl !== "#") {
                // Check if URL starts with http/https
                if (
                    attachmentUrl.startsWith("http://") ||
                    attachmentUrl.startsWith("https://")
                ) {
                    window.open(attachmentUrl, "_blank");
                } else {
                    // Assume it's a relative path
                    window.open(`/${attachmentUrl}`, "_blank");
                }
            } else {
                this.showError("Không tìm thấy file đính kèm");
            }
        },

        showSuccess(message) {
            Swal.fire({
                icon: "success",
                title: "Thành công",
                text: message,
                timer: 2000,
                timerProgressBar: true,
            });
        },

        showError(message) {
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: message,
            });
        },

        goBack() {
            this.$router.go(-1);
        },
    },
};
</script>
<style scoped>
/* Sticky container */
.sticky-wrapper {
    position: sticky;
    top: 0px;
    left: 230px;
    right: 0;
    z-index: 999;
    background: white;
    padding: 1rem 0;
    border-bottom: 1px solid #e0e3e8;
    transition: box-shadow 0.3s ease;
}

/* Toggle section styles */
.toggle-section {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.toggle-icon {
    transition: transform 0.3s ease;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
}

.cursor-pointer {
    cursor: pointer;
}

.card-header {
    padding: 1rem 1.25rem;
    transition: background-color 0.2s ease;
}

.card-header:hover {
    background-color: rgba(0, 0, 0, 0.01);
}

/* Transition effects for card body */
.card-body {
    transition: all 0.3s ease;
}

/* Main content wrapper */
.main-content-wrapper {
    margin-top: 10px;
    padding: 1rem;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scroll-area::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge */
.scroll-area {
    -ms-overflow-style: none;
    scrollbar-width: none;
    height: calc(90vh - 60px);
    -webkit-overflow-scrolling: touch;
}

/* Customize scrollbar styling */
.ps__rail-y {
    width: 9px;
    background-color: transparent !important;
}

.ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
}

/* Hover styling for scrollbar */
.ps__rail-y:hover > .ps__thumb-y,
.ps__rail-y:focus > .ps__thumb-y,
.ps__rail-y.ps--clicking .ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.3);
}

/* Responsive styles */
@media (max-width: 768px) {
    .sticky-wrapper {
        top: 0px;
        left: 0;
        padding: 0.5rem 0;
        z-index: 100;
    }

    .main-content-wrapper {
        margin-top: 10px;
    }

    .d-flex.flex-md-row {
        flex-direction: column !important;
    }

    .col-md-6 {
        width: 100% !important;
    }
}

/* Enhanced table styles */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}

.table th {
    font-weight: 600;
    white-space: nowrap;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table td,
.table th {
    padding: 0.75rem;
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.03);
}

/* Empty state styling */
.empty-state {
    text-align: center;
    padding: 30px 0;
    color: #6c757d;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

/* Card styling */
.card {
    border: none;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border-radius: 5px;
    overflow: hidden;
}

/* Table Professional Enhancement */
.table {
    font-size: 0.8125rem; /* 13px */
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e6e6e6;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

/* Table Header Styling */
.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.03em;
    padding: 12px 10px;
    border-bottom: 2px solid #e9ecef;
    position: sticky;
    top: 0;
    z-index: 5;
}

/* Improved cell padding for better readability */
.table td {
    padding: 10px;
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
    border-top: 1px solid #f0f0f0;
    transition: all 0.2s;
}

/* Tooltip for truncated content */
.table td[title] {
    cursor: help;
}

/* Add hover style to see more of truncated content */
.table td:hover {
    overflow: visible;
    white-space: normal;
    max-width: none;
    position: relative;
    z-index: 1;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

/* Zebra striping for easier row scanning */
.table tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.01);
}

/* Row hover effect */
.table-hover tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
}

/* Align numerical data right */
.table td.text-end,
.table th.text-end {
    text-align: right;
}

/* Highlight active row - add .active class to tr when needed */
.table tr.active {
    background-color: rgba(16, 185, 129, 0.1) !important;
}

/* Footer styling */
.table tfoot {
    font-weight: 600;
    background-color: #f8f9fa;
}

.table tfoot td {
    border-top: 2px solid #e9ecef;
}

/* Note styling */
.note-container {
    background-color: #f9f9f9;
    border: 1px solid #e6e6e6;
    border-radius: 4px;
    padding: 10px 15px;
    min-height: 60px;
    transition: all 0.2s ease;
}

.note-container:hover {
    border-color: #d1d1d1;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.note-content {
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.9rem;
    line-height: 1.5;
}

.note-content .text-muted {
    font-size: 0.85rem;
}
</style>
