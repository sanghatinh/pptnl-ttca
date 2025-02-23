<template lang="">
    <div class="row align-items-center mb-2">
        <div class="col d-flex gap-3">
            <button
                v-if="showSaveButton"
                type="button"
                class="button-30"
                @click="saveOrUpdateDocument"
            >
                <i class="bx bxs-save"></i>Save
            </button>

            <button
                v-if="showCreateNewButton"
                type="button"
                class="button-30-save"
                @click="createNew"
            >
                <i class="fa-solid fa-plus"></i>Tạo mới
            </button>

            <button
                v-if="showSubmitButton"
                class="button-30"
                role="button"
                @click="sendDocument"
            >
                <i class="bx bx-calendar-check"></i>Nộp
            </button>

            <button
                v-if="showRejectButton"
                type="button"
                class="button-30"
                @click="rejectDocument"
            >
                <i class="bx bx-calendar-x"></i>Không duyệt
            </button>

            <button
                v-if="showApproveButton"
                type="button"
                class="button-30"
                @click="receiveDocument"
            >
                <i class="bx bx-check-square"></i>Duyệt
            </button>

            <button
                v-if="showCancelButton"
                type="button"
                class="button-30"
                @click="cancelDocument"
            >
                <i class="fa-solid fa-xmark"></i>Hủy
            </button>

            <button
                v-if="showDeleteButton"
                type="button"
                class="button-30"
                @click="deleteDocument"
            >
                <i class="fa-solid fa-trash-can"></i>Xóa
            </button>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <!-- progress-tracker-container -->
            <div class="col-12">
                <div class="progress-tracker" :class="document.status">
                    <!-- Creating Step -->
                    <div
                        class="track-step"
                        :class="{ active: document.status === 'creating' }"
                    >
                        <div class="step-icon status-creating">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <span class="step-label">Nháp</span>
                    </div>

                    <!-- Sending Step -->
                    <div
                        class="track-step"
                        :class="{ active: document.status === 'sending' }"
                    >
                        <div class="step-icon status-sending">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <span class="step-label">Đang nộp</span>
                    </div>

                    <!-- Received Step -->
                    <div
                        class="track-step"
                        :class="{ active: document.status === 'received' }"
                    >
                        <div class="step-icon status-received">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="step-label">Đã nhận</span>
                    </div>

                    <!-- Cancelled Step -->
                    <div
                        v-if="showCancelledStep"
                        class="track-step"
                        :class="{ active: document.status === 'cancelled' }"
                    >
                        <div class="step-icon status-cancelled">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <span class="step-label">Hủy</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-1">
                <!-- ส่วนที่ 1 ของการสร้างหน้าจอ -->
                <div class="card col-12 col-md-6">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin phiếu</h5>
                        <div class="row gutters">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Mã số phiếu&nbsp;<span
                                            ><i
                                                class="bx bx-lock-alt"
                                            ></i></span
                                    ></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.document_code"
                                        placeholder="Mã số phiếu"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Ngày lập<span
                                            v-if="
                                                formValidation.createdDate
                                                    .required
                                            "
                                            class="text-danger"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        type="date"
                                        class="form-control"
                                        v-model="document.created_date"
                                        placeholder="Ngày lập"
                                        :class="{
                                            'is-invalid':
                                                formValidation.createdDate
                                                    .required,
                                        }"
                                    />
                                    <div
                                        v-if="
                                            formValidation.createdDate.required
                                        "
                                        class="invalid-feedback"
                                    >
                                        {{ formValidation.createdDate.message }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Tiều đề&nbsp;<span
                                            ><i
                                                class="bx bx-lock-alt"
                                            ></i></span
                                    ></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.title"
                                        placeholder="Tiều đề"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Vụ đầu tư<span
                                            v-if="
                                                formValidation.investmentProject
                                                    .required
                                            "
                                            class="text-danger"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        class="form-control"
                                        v-model="document.investment_project"
                                        :class="{
                                            'is-invalid':
                                                formValidation.investmentProject
                                                    .required,
                                        }"
                                    >
                                        <option
                                            v-for="project in investmentProjects"
                                            :key="project.id"
                                            :value="project.Ten_Vudautu"
                                        >
                                            {{ project.Ten_Vudautu }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="
                                            formValidation.investmentProject
                                                .required
                                        "
                                        class="invalid-feedback"
                                    >
                                        {{
                                            formValidation.investmentProject
                                                .message
                                        }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Người tạo&nbsp;<span
                                            ><i
                                                class="bx bx-lock-alt"
                                            ></i></span
                                    ></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="user ? user.full_name : ''"
                                        disabled
                                    />
                                </div>

                                <!-- <div class="form-group">
                                    <label for="">ID Người tạo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="document.creator_id"
                                        disabled
                                    />
                                </div> -->
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Trạm&nbsp;<span
                                            ><i
                                                class="bx bx-lock-alt"
                                            ></i></span
                                    ></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="user ? user.station : ''"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Loại hồ sơ<span
                                            v-if="
                                                formValidation.documentType
                                                    .required
                                            "
                                            class="text-danger"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        class="form-control"
                                        v-model="document.document_type"
                                        :class="{
                                            'is-invalid':
                                                formValidation.documentType
                                                    .required,
                                        }"
                                    >
                                        <option value="">
                                            Chọn loại hồ sơ
                                        </option>
                                        <option
                                            v-for="type in documentTypes"
                                            :key="type.id"
                                            :value="type.Ten_LoaiHoso"
                                        >
                                            {{ type.Ten_LoaiHoso }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="
                                            formValidation.documentType.required
                                        "
                                        class="invalid-feedback"
                                    >
                                        {{
                                            formValidation.documentType.message
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ส่วนที่ 2 ของการสร้างหน้าจอ -->
                <div class="card col-12 col-md-6">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin giao nhận</h5>
                        <div class="row gutters">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for=""
                                        >Số lượng hồ sơ&nbsp;<span
                                            ><i
                                                class="bx bx-lock-alt"
                                            ></i></span
                                    ></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="documentCount"
                                        placeholder="Số lượng hồ sơ"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group" v-if="showreceivedate">
                                    <label for=""
                                        >Ngày nhận<span
                                            v-if="
                                                formValidation.receivedDate
                                                    .required
                                            "
                                            class="text-danger"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        type="date"
                                        class="form-control"
                                        v-model="document.received_date"
                                        placeholder="Ngày nhận"
                                        :class="{
                                            'is-invalid':
                                                formValidation.receivedDate
                                                    .required,
                                        }"
                                    />
                                    <div
                                        v-if="
                                            formValidation.receivedDate.required
                                        "
                                        class="invalid-feedback"
                                    >
                                        {{
                                            formValidation.receivedDate.message
                                        }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div
                                    class="form-group"
                                    v-if="showReceiverField"
                                >
                                    <label for=""
                                        >Người nhận&nbsp;<span
                                            ><i
                                                class="bx bx-lock-alt"
                                            ></i></span
                                    ></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="user ? user.full_name : ''"
                                        disabled
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ส่วนที่ 3 ของการสร้าง รายละเอียดตาลาง -->
            <div v-if="showNghiemThu" class="card add-bienban-nghiemthu-dichvu">
                <div class="card-body">
                    <div class="row">
                        <h5 class="col card-title">
                            BIÊN BẢN NGHIỆM THU DỊCH VỤ
                        </h5>
                        <div class="col text-end">
                            <button
                                v-if="canModifyMappings"
                                type="button"
                                class="btn btn-success btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#extraLargeModal"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Mã nghiệm thu</th>
                                    <th>Trạm</th>
                                    <th>Vụ đầu tư</th>
                                    <th>Tiêu đề</th>
                                    <th>Khách hàng cá nhân ĐT mía</th>
                                    <th>Khách hàng doanh nghiệp ĐT mía</th>
                                    <th>Hợp đồng đầu tư mía</th>
                                    <th>Hình thức thực hiện DV</th>
                                    <th>Hợp đồng cung ứng dịch vụ</th>
                                    <th>Tổng tiền</th>
                                    <th>Tổng tiền dịch vụ</th>
                                    <th>Tổng tiền tạm giữ</th>
                                    <th>Tổng tiền thanh toán</th>
                                    <th>Cán bộ nông vụ</th>
                                    <th>Tình trạng</th>
                                    <th>Tình trạng duyệt</th>
                                    <th>Ngày tạo</th>
                                    <th>Ngày cập nhật</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in mappedDocuments"
                                    :key="item.mapping_id"
                                >
                                    <td>
                                        <button
                                            v-if="canModifyMappings"
                                            @click="
                                                deleteMapping(item.mapping_id)
                                            "
                                            class="btn btn-danger btn-sm"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                    <td>{{ item.ma_nghiem_thu }}</td>
                                    <td>{{ item.station }}</td>
                                    <td>{{ item.vu_dau_tu }}</td>
                                    <td>{{ item.title }}</td>
                                    <td>{{ item.khach_hang_ca_nhan }}</td>
                                    <td>{{ item.khach_hang_doanh_nghiep }}</td>
                                    <td>{{ item.hop_dong_dau_tu_mia }}</td>
                                    <td>{{ item.hinh_thuc_thuc_hien_dv }}</td>
                                    <td>
                                        {{ item.hop_dong_cung_ung_dich_vu }}
                                    </td>
                                    <td>{{ item.tong_tien }}</td>
                                    <td>{{ item.tong_tien_dich_vu }}</td>
                                    <td>{{ item.tong_tien_tam_giu }}</td>
                                    <td>{{ item.tong_tien_thanh_toan }}</td>
                                    <td>{{ item.can_bo_nong_vu }}</td>
                                    <td>{{ item.tinh_trang }}</td>
                                    <td>{{ item.tinh_trang_duyet }}</td>
                                    <td>{{ item.created_at }}</td>
                                    <td>{{ item.updated_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ส่วนที่ 4 ของการสร้าง รายละเอียดตาลาง -->
            <div v-if="showHomGiong" class="card add-bienban-hom-giong">
                <div class="card-body">
                    <div class="row">
                        <h5 class="col card-title">BIÊN BẢN HOM GIỐNG</h5>
                        <div class="col text-end">
                            <button
                                v-if="canModifyMappings"
                                type="button"
                                class="btn btn-success btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#homGiongModal"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Mã số phiếu</th>
                                    <th>Cán bộ nông vụ</th>
                                    <th>Tên phiếu</th>
                                    <th>Hợp đồng đầu tư mía bên giao</th>
                                    <th>Hợp đồng đầu tư mía bên nhận</th>
                                    <th>Tổng thực nhận</th>
                                    <th>Tổng tiền</th>
                                    <th>Vụ đầu tư</th>
                                    <th>Tình trạng duyệt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in mappedHomGiongDocuments"
                                    :key="item.mapping_id"
                                >
                                    <td>
                                        <button
                                            v-if="canModifyMappings"
                                            @click="
                                                deleteHomGiongMapping(
                                                    item.mapping_id
                                                )
                                            "
                                            class="btn btn-danger btn-sm"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                    <td>{{ item.ma_so_phieu }}</td>
                                    <td>{{ item.can_bo_nong_vu }}</td>
                                    <td>{{ item.ten_phieu }}</td>
                                    <td>
                                        {{ item.hop_dong_dau_tu_mia_ben_giao }}
                                    </td>
                                    <td>
                                        {{ item.hop_dong_dau_tu_mia_ben_nhan }}
                                    </td>
                                    <td>{{ item.tong_thuc_nhan }}</td>
                                    <td>{{ item.tong_tien }}</td>
                                    <td>{{ item.vu_dau_tu }}</td>
                                    <td>{{ item.tinh_trang_duyet }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal add nghiem thu dich vu -->
    <div
        class="modal fade"
        id="extraLargeModal"
        tabindex="-1"
        aria-labelledby="extraLargeModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="extraLargeModalLabel">
                        ADD BIÊN BẢN NGHIỆM THU DỊCH VỤ
                    </h6>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body justify-content-center">
                    <div class="col">
                        <div class="form-group">
                            <label for="bienBanSearch"
                                >Tìm kiếm Mã nghiệm thu + Chọn Mã nghiệm
                                thu</label
                            >
                            <input
                                type="search"
                                class="form-control"
                                v-model="searchQuery"
                                @input="searchBienBan"
                                :placeholder="
                                    selectedBienBan
                                        ? selectedBienBan.ma_nghiem_thu
                                        : 'Nhập mã nghiệm thu...'
                                "
                            />
                            <ul
                                v-if="bienBanResults.length"
                                class="dropdown-menu show"
                            >
                                <li
                                    v-for="item in bienBanResults"
                                    :key="item.ma_nghiem_thu"
                                    @click="selectBienBan(item)"
                                    class="dropdown-item"
                                >
                                    {{ item.ma_nghiem_thu }}
                                </li>
                            </ul>
                        </div>
                        <div class="form-group mt-2">
                            <label for="hopDongDauTuMia"
                                >Hợp đồng đầu tư mía</label
                            >
                            <input
                                type="text"
                                class="form-control"
                                :value="
                                    selectedBienBan
                                        ? selectedBienBan.hop_dong_dau_tu_mia
                                        : ''
                                "
                                disabled
                            />
                        </div>
                        <div class="form-group mt-2">
                            <label for="hinhThucThucHienDV"
                                >Hình thức thực hiện DV</label
                            >
                            <input
                                type="text"
                                class="form-control"
                                :value="
                                    selectedBienBan
                                        ? selectedBienBan.hinh_thuc_thuc_hien_dv
                                        : ''
                                "
                                disabled
                            />
                        </div>
                        <div class="form-group mt-2">
                            <label for="hopDongCungUngDV"
                                >Hợp đồng cung ứng dịch vụ</label
                            >
                            <input
                                type="text"
                                class="form-control"
                                :value="
                                    selectedBienBan
                                        ? selectedBienBan.hop_dong_cung_ung_dich_vu
                                        : ''
                                "
                                disabled
                            />
                        </div>
                        <div class="form-group mt-2">
                            <label for="tongTienDichVu"
                                >Tổng tiền dịch vụ</label
                            >
                            <input
                                type="text"
                                class="form-control"
                                :value="
                                    selectedBienBan
                                        ? selectedBienBan.tong_tien_dich_vu
                                        : ''
                                "
                                disabled
                            />
                        </div>
                        <div class="col d-flex gap-3 mt-3">
                            <button
                                type="button"
                                class="button-30-save"
                                @click="addMapping(selectedBienBan)"
                            >
                                <i class="fa-solid fa-plus"></i>Add & Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--modal  Add bien ban nghiem thu hom giong -->
    <div
        class="modal fade"
        id="homGiongModal"
        tabindex="-1"
        aria-labelledby="homGiongModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="homGiongModalLabel">
                        ADD BIÊN BẢN HOM GIỐNG
                    </h6>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body justify-content-center">
                    <div class="col">
                        <div class="form-group">
                            <label for="homGiongSearch"
                                >Tìm kiếm Mã số phiếu</label
                            >
                            <input
                                type="search"
                                class="form-control"
                                v-model="searchHomGiongQuery"
                                @input="searchHomGiong"
                                :placeholder="
                                    selectedHomGiong
                                        ? selectedHomGiong.ma_so_phieu
                                        : 'Nhập mã số phiếu...'
                                "
                            />
                            <ul
                                v-if="homGiongResults.length"
                                class="dropdown-menu show"
                            >
                                <li
                                    v-for="item in homGiongResults"
                                    :key="item.ma_so_phieu"
                                    @click="selectHomGiong(item)"
                                    class="dropdown-item"
                                >
                                    {{ item.ma_so_phieu }}
                                </li>
                            </ul>
                        </div>
                        <div class="form-group mt-2">
                            <label>Cán bộ nông vụ</label>
                            <input
                                type="text"
                                class="form-control"
                                :value="
                                    selectedHomGiong
                                        ? selectedHomGiong.can_bo_nong_vu
                                        : ''
                                "
                                disabled
                            />
                        </div>
                        <div class="form-group mt-2">
                            <label>Tên phiếu</label>
                            <input
                                type="text"
                                class="form-control"
                                :value="
                                    selectedHomGiong
                                        ? selectedHomGiong.ten_phieu
                                        : ''
                                "
                                disabled
                            />
                        </div>
                        <div class="form-group mt-2">
                            <label>Tổng thực nhận</label>
                            <input
                                type="text"
                                class="form-control"
                                :value="
                                    selectedHomGiong
                                        ? selectedHomGiong.tong_thuc_nhan
                                        : ''
                                "
                                disabled
                            />
                        </div>
                        <div class="col d-flex gap-3 mt-3">
                            <button
                                type="button"
                                class="button-30-save"
                                @click="addHomGiongMapping(selectedHomGiong)"
                            >
                                <i class="fa-solid fa-plus"></i>Add & Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            document: {
                document_code: "",
                created_date: "",
                title: "",
                investment_project: "",
                document_type: "", // will hold one of: 'Nghiệm thu dịch vụ', 'Hom giống', 'Chọn cả 2'
                file_count: 0,
                received_date: "",
                status: "creating",
                creator_id: null,
                receiver_id: null,
                creator_name: "",
                station: "",
                searchQuery: "",
                searchHomGiongQuery: "",
            },
            investmentProjects: [],
            mappedDocuments: [],

            // Use a static array for demo purposes. You may later fetch these from #TABLE tb_loaihoso.
            documentTypes: [
                { id: 1, Ten_LoaiHoso: "Nghiệm thu dịch vụ" },
                { id: 2, Ten_LoaiHoso: "Hom giống" },
                { id: 3, Ten_LoaiHoso: "Chọn cả 2" },
            ],
            user: null,
            receiver: null,
            bienBanResults: [],
            selectedBienBan: null,
            homGiongResults: [],
            selectedHomGiong: null,
            mappedHomGiongDocuments: [],
        };
    },
    computed: {
        showNghiemThu() {
            return (
                this.document.document_type === "Nghiệm thu dịch vụ" ||
                this.document.document_type === "Chọn cả 2"
            );
        },
        showHomGiong() {
            return (
                this.document.document_type === "Hom giống" ||
                this.document.document_type === "Chọn cả 2"
            );
        },

        // Add these computed properties
        showSaveButton() {
            return !this.document.id || this.document.status === "creating";
        },
        showCreateNewButton() {
            return this.document.id; // Show when document exists
        },
        showSubmitButton() {
            return this.document.status === "creating";
        },
        showApproveButton() {
            return this.document.status === "sending";
        },
        showRejectButton() {
            return this.document.status === "sending";
        },
        showCancelButton() {
            return this.document.status === "received";
        },
        showDeleteButton() {
            return this.document.id; // Show when document exists
        },
        showCancelledStep() {
            return this.document.status === "cancelled";
        },

        formValidation() {
            return {
                createdDate: {
                    required: !this.document.created_date,
                    message: "Vui lòng nhập ngày lập",
                },
                receivedDate: {
                    required:
                        this.document.status === "sending" &&
                        !this.document.received_date,
                    message: "Vui lòng nhập ngày nhận",
                },
                investmentProject: {
                    required: !this.document.investment_project,
                    message: "Vui lòng chọn vụ đầu tư",
                },
                documentType: {
                    required: !this.document.document_type,
                    message: "Vui lòng chọn loại hồ sơ",
                },
            };
        },
        showReceiverField() {
            return ["received", "cancelled"].includes(this.document.status);
        },
        showreceivedate() {
            return ["sending", "received", "cancelled"].includes(
                this.document.status
            );
        },
        canModifyMappings() {
            return !["sending", "received", "cancelled"].includes(
                this.document.status
            );
        },
        documentCount() {
            // Count both nghiem thu and hom giong documents
            const nghiemThuCount = this.mappedDocuments.length;
            const homGiongCount = this.mappedHomGiongDocuments.length;
            return nghiemThuCount + homGiongCount;
        },
    },
    mounted() {
        this.fetchUserData();
        this.fetchInvestmentProjects();

        // still fetchDocumentTypes if needed or use static one above
    },
    methods: {
        // Add these new methods
        saveOrUpdateDocument() {
            if (this.document.id) {
                // Update existing document
                axios
                    .put(
                        `/api/document-deliveries/${this.document.id}`,
                        this.document
                    )
                    .then((response) => {
                        this.document = response.data;
                        alert("Document updated successfully");
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            } else {
                // Create new document
                this.saveDocument();
            }
        },

        createNew() {
            // Reset form data
            this.document = {
                document_code: "",
                created_date: "",
                title: "",
                investment_project: "",
                document_type: "",
                file_count: 0,
                received_date: "",
                status: "creating",
                creator_id: this.user?.id,
                receiver_id: this.user?.id,
                creator_name: this.user?.full_name,
                station: this.user?.station,
                searchQuery: "",
                searchHomGiongQuery: "",
            };
            this.selectedBienBan = null;
            this.mappedDocuments = [];
            this.bienBanResults = [];
            this.selectedHomGiong = null;
            this.mappedHomGiongDocuments = [];
            this.homGiongResults = [];
        },

        deleteDocument() {
            if (!this.document.id) return;

            if (confirm("Are you sure you want to delete this document?")) {
                axios
                    .delete(`/api/document-deliveries/${this.document.id}`)
                    .then(() => {
                        alert("Document deleted successfully");
                        this.createNew(); // Reset form after delete
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
        },

        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                const parsedUser = JSON.parse(user);
                this.user = parsedUser;
                this.document.creator_id = parsedUser.id;
                this.document.creator_name = parsedUser.full_name;
                this.document.station = parsedUser.station;
                this.document.receiver_id = parsedUser.id;
            }
        },
        fetchInvestmentProjects() {
            axios
                .get("/api/investment-projects")
                .then((response) => {
                    this.investmentProjects = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching investment projects:", error);
                });
        },
        // Update saveDocument method
        saveDocument() {
            // Check validation before saving

            if (this.formValidation.createdDate.required) {
                alert("Vui lòng nhập ngày lập trước khi lưu");
                return;
            }

            if (this.formValidation.receivedDate.required) {
                alert("Vui lòng nhập ngày nhận trước khi lưu");
                return;
            }

            if (this.formValidation.investmentProject.required) {
                alert("Vui lòng chọn vụ đầu tư trước khi lưu");
                return;
            }

            if (this.formValidation.documentType.required) {
                alert("Vui lòng chọn loại hồ sơ trước khi lưu");
                return;
            }

            // Update file count before saving
            this.document.file_count = this.documentCount;

            axios
                .post("/api/document-deliveries", this.document)
                .then((response) => {
                    this.document = response.data;
                    alert("Document saved successfully");
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        // The existing status update methods...
        sendDocument() {
            this.updateDocumentStatus("sending");
        },
        rejectDocument() {
            this.updateDocumentStatus("creating");
        },
        receiveDocument() {
            this.updateDocumentStatus("received");
        },
        cancelDocument() {
            this.updateDocumentStatus("cancelled");
        },
        updateDocumentStatus(status) {
            axios
                .patch(`/api/document-deliveries/${this.document.id}/status`, {
                    status: status,
                    received_date: new Date().toISOString().split("T")[0],
                    receiver_id: this.user.id,
                })
                .then((response) => {
                    this.document = response.data;
                    alert(`Document status updated to ${status}`);
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        // NEW: Search for ma_nghiem_thu when the modal shows up.
        searchBienBan() {
            axios
                .get("/api/bienban-nghiemthu-search", {
                    params: {
                        search: this.searchQuery,
                        investment_project: this.document.investment_project,
                        station: this.user.station,
                    },
                })
                .then((response) => {
                    this.bienBanResults = response.data;
                    console.log("Search results:", response.data);
                })
                .catch((error) => {
                    console.error(error);
                    console.error("Search error:", error);
                });
        },
        selectBienBan(item) {
            this.selectedBienBan = item;
            this.searchQuery = item.ma_nghiem_thu; // Set the search input value to selected ma_nghiem_thu
            this.showBienBanDetails();
            this.bienBanResults = []; // Clear the dropdown after selection
        },
        showBienBanDetails() {
            // This method will be triggered when a bien ban is selected
            // It will automatically bind the selectedBienBan details to the form fields
            if (this.selectedBienBan) {
                this.document.hop_dong_dau_tu_mia =
                    this.selectedBienBan.hop_dong_dau_tu_mia;
                this.document.hinh_thuc_thuc_hien_dv =
                    this.selectedBienBan.hinh_thuc_thuc_hien_dv;
                this.document.hop_dong_cung_ung_dich_vu =
                    this.selectedBienBan.hop_dong_cung_ung_dich_vu;
                this.document.tong_tien_dich_vu =
                    this.selectedBienBan.tong_tien_dich_vu;
            }
        },
        addMapping(selected) {
            axios
                .post("/api/document-mappings", {
                    document_code: this.document.document_code,
                    ma_nghiem_thu: selected.ma_nghiem_thu,
                })
                .then((response) => {
                    alert("Mapping added successfully");
                    this.fetchMappedDocuments(); // Refresh the table
                    // Close modal
                    const modal = document.getElementById("extraLargeModal");
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                })
                .catch((error) => {
                    if (error.response && error.response.status === 422) {
                        alert(
                            "Cannot add this mapping: " +
                                error.response.data.error
                        );
                    } else {
                        console.error("Error adding mapping:", error);
                        alert("An error occurred while adding the mapping");
                    }
                });
        },
        fetchMappedDocuments() {
            if (!this.document.document_code) return;

            axios
                .get(`/api/document-mappings/${this.document.document_code}`)
                .then((response) => {
                    this.mappedDocuments = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching mapped documents:", error);
                });
        },

        deleteMapping(mappingId) {
            if (confirm("Are you sure you want to delete this mapping?")) {
                axios
                    .delete(`/api/document-mappings/${mappingId}`)
                    .then((response) => {
                        alert("Mapping deleted");
                        this.fetchMappedDocuments(); // Refresh the table
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
        },
        //modal add hom giong
        searchHomGiong() {
            axios
                .get("/api/bienban-homgiong-search", {
                    params: {
                        search: this.searchHomGiongQuery,
                        investment_project: this.document.investment_project,
                        station: this.user.station,
                    },
                })
                .then((response) => {
                    this.homGiongResults = response.data;
                })
                .catch((error) => {
                    console.error("Search error:", error);
                });
        },

        selectHomGiong(item) {
            this.selectedHomGiong = item;
            this.searchHomGiongQuery = item.ma_so_phieu;
            this.homGiongResults = [];
        },

        addHomGiongMapping(selected) {
            if (!selected) return;

            axios
                .post("/api/document-mappings-homgiong", {
                    document_code: this.document.document_code,
                    ma_so_phieu: selected.ma_so_phieu,
                })
                .then((response) => {
                    alert("Hom Giong mapping added successfully");
                    this.fetchMappedHomGiongDocuments();
                    // Close modal
                    const modal = document.getElementById("homGiongModal");
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                })
                .catch((error) => {
                    if (error.response && error.response.status === 422) {
                        alert(
                            "Cannot add this mapping: " +
                                error.response.data.error
                        );
                    } else {
                        console.error("Error adding mapping:", error);
                        alert("An error occurred while adding the mapping");
                    }
                });
        },
        deleteHomGiongMapping(mappingId) {
            if (confirm("Are you sure you want to delete this mapping?")) {
                axios
                    .delete(`/api/document-mappings-homgiong/${mappingId}`)
                    .then((response) => {
                        alert("Hom Giong mapping deleted successfully");
                        this.fetchMappedHomGiongDocuments(); // Refresh the table
                    })
                    .catch((error) => {
                        console.error("Error deleting mapping:", error);
                        alert("An error occurred while deleting the mapping");
                    });
            }
        },

        fetchMappedHomGiongDocuments() {
            if (!this.document.document_code) return;

            axios
                .get(
                    `/api/document-mappings-homgiong/${this.document.document_code}`
                )
                .then((response) => {
                    this.mappedHomGiongDocuments = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching mapped documents:", error);
                });
        },
    },

    // Add mounted or watch hook to load data when document_code changes:
    watch: {
        "document.document_code": {
            handler(newVal) {
                if (newVal) {
                    this.fetchMappedDocuments();
                    this.fetchMappedHomGiongDocuments(); // Add this line to fetch Hom Giong documents
                } else {
                    this.mappedDocuments = [];
                    this.mappedHomGiongDocuments = [];
                }
            },
            immediate: true,
        },
    },
};
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
    width: 98%;
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
