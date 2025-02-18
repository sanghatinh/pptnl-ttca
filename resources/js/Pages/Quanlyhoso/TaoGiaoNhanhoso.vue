<template lang="">
    <div class="row align-items-center mb-2">
        <div class="col d-flex gap-3">
            <router-link to="/taonewhoso">
                <button type="button" class="btn btn-success btn-sm">
                    <i class="bx bxs-save"></i>Save
                </button>
            </router-link>
            <button type="button" class="btn btn-info btn-sm">
                <i class="bx bx-calendar-check"></i>Nộp
            </button>
            <button type="button" class="btn btn-secondary btn-sm">
                <i class="bx bx-calendar-x"></i>Không duyệt
            </button>
            <button type="button" class="btn btn-primary btn-sm">
                <i class="bx bx-check-square"></i>Duyệt
            </button>
            <button type="button" class="btn btn-warning btn-sm">
                <i class="fa-solid fa-xmark"></i>Hủy
            </button>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <!-- progress-tracker-container -->
            <div class="col-12">
                <div class="progress-tracker">
                    <!-- Creating Step -->
                    <div class="track-step active">
                        <div class="step-icon status-creating">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <span class="step-label">Nháp</span>
                    </div>

                    <!-- Sending Step -->
                    <div class="track-step">
                        <div class="step-icon status-sending">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <span class="step-label">Đang nộp</span>
                    </div>

                    <!-- Received Step -->
                    <div class="track-step">
                        <div class="step-icon status-received">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="step-label">Đã nhận</span>
                    </div>

                    <!-- Cancelled Step -->
                    <div class="track-step">
                        <div class="step-icon status-cancelled">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <span class="step-label">Hủy</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-1">
                <!-- ส่วนที่ 1 ของการสร้างหน้าจอ -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin phiếu</h5>
                        <div class="row gutters">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Mã số phiếu</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Mã số phiếu"
                                        disabled=""
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Tiều đề</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Tiều đề"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Loại hồ sơ</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Loại hồ sơ"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Số lượng hồ sơ</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Số lượng hồ sơ"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Tình trạng thanh toán</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Số lượng hồ sơ"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ส่วนที่ 2 ของการสร้างหน้าจอ -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin giao nhận</h5>
                        <div class="row gutters">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Người tạo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Người tạo"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Trạm</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Trạm"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Ngày lập</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        placeholder="Ngày lập"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Người nhận</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Người nhận"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Ngày nhận</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        placeholder="Ngày nhận"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ส่วนที่ 3 ของการสร้าง รายละเอียดตาลาง -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5 class="col card-title">
                            BIÊN BẢN NGHIỆM THU DỊCH VỤ
                        </h5>
                        <div class="col text-end">
                            <button
                                type="button"
                                class="btn btn-success btn-sm"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Trạm</th>
                                    <th>Cán bộ nông vụ</th>
                                    <th>Mã nghiệm thu</th>
                                    <th>Tiêu đề</th>
                                    <th>Khách hàng doanh nghiệp</th>
                                    <th>Khách hàng cá nhân</th>
                                    <th>Đối tác cá nhân cung cấp dịch vụ</th>
                                    <th>Đối tác cung cấp dịch vụ (KHDN)</th>
                                    <th>Mã số thửa</th>
                                    <th>Hình thức thực hiện</th>
                                    <th>Dịch vụ</th>
                                    <th>Nghiệm thu dịch vụ</th>
                                    <th>Đơn vị tính</th>
                                    <th>Khối lượng thực hiện</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                    <th>Tiền thanh toán</th>
                                    <th>Tiền thanh toán còn lại</th>
                                    <th>Số lần thực hiện</th>
                                    <th>Đã thanh toán</th>
                                    <th>Vụ đầu tư</th>
                                    <th>Tình trạng duyệt</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ส่วนที่ 4 ของการสร้าง รายละเอียดตาลาง -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5 class="col card-title">BIÊN BẢN HOM GIỐNG</h5>
                        <div class="col text-end">
                            <button
                                type="button"
                                class="btn btn-success btn-sm"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã số phiếu</th>
                                    <th>Ngày lập</th>
                                    <th>Người tạo</th>
                                    <th>Trạm</th>
                                    <th>Loại hồ sơ</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {};
</script>
<style scoped>
.table th,
.table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12px;
}

.progress-tracker {
    position: relative;
    display: flex;
    justify-content: space-between;
    margin: 0.5rem 0;
}
.progress-tracker::before {
    content: "";
    position: absolute;
    top: 20px;
    width: 100%;
    height: 3px;
    background-color: #e9ecef;
    z-index: 0;
    transition: background-color 0.3s;
}
.progress-tracker.creating::before {
    background-color: #fdd835;
}
.progress-tracker.sending::before {
    background-color: #1e88e5;
}
.progress-tracker.received::before {
    background-color: #198754;
}
.progress-tracker.cancelled::before {
    background-color: #dc3545;
}
.track-step {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 1;
}
.step-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: white;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    border: 2px solid #dee2e6;
    transition: all 0.3s;
}
.track-step.active .step-icon {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    border: none;
}
.status-creating .step-icon {
    color: #fdd835;
}
.status-sending .step-icon {
    color: #1e88e5;
}
.status-received .step-icon {
    color: #198754;
}
.status-cancelled .step-icon {
    color: #dc3545;
}
.track-step.active .status-creating {
    background-color: #fdd835;
    color: white;
}
.track-step.active .status-sending {
    background-color: #1e88e5;
    color: white;
}
.track-step.active .status-received {
    background-color: #198754;
    color: white;
}
.track-step.active .status-cancelled {
    background-color: #dc3545;
    color: white;
}
.step-label {
    text-align: center;
    font-size: 0.9rem;
    max-width: 100px;
}
@media (max-width: 768px) {
    /* .progress-tracker {
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
    }
    .progress-tracker::before {
        display: none;
    } */
}
</style>
