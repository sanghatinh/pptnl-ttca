<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div>
        <div class="row align-items-center mb-4 mt-2">
            <!-- Show "Tạo mới" button only when user has 'create' permission -->
            <div class="col-12" v-if="!ShowFormUser">
                <div
                    class="d-flex flex-row justify-content-between align-items-center gap-2"
                >
                    <div class="button-container">
                        <button
                            v-if="userHasPermission('create')"
                            type="button"
                            @click="Adduser"
                            class="button-30-save d-flex align-items-center"
                        >
                            <i class="bx bxs-user-plus me-1"></i>
                            <span>Tạo mới</span>
                        </button>
                    </div>
                    <div class="search-container">
                        <div class="input-group">
                            <span
                                class="input-group-text bg-white border-end-0"
                            >
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search users..."
                                class="form-control border-start-0 search-input"
                                aria-label="Search"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-2" v-if="ShowFormUser">
                <div class="d-flex justify-content-end">
                    <button
                        type="button"
                        @click="Saveuser"
                        :disabled="FormValid"
                        class="button-30-save me-2 d-flex align-items-center"
                    >
                        <i class="bx bxs-save me-1"></i>
                        <span>Save</span>
                    </button>
                    <button
                        type="button"
                        @click="Canneluser"
                        class="button-30 d-flex align-items-center"
                    >
                        <i class="bx bx-x me-1"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <!-- แสดง From เพี่ม และ แก้ไข User -->
                <div v-if="ShowFormUser">
                    <div class="row d-flex">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div
                                            class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                        >
                                            <div class="form-group mb-3">
                                                <label for="inputName"
                                                    >Username<span
                                                        v-if="errors.username"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="FormUser.username"
                                                    placeholder="username"
                                                    required
                                                />
                                            </div>
                                        </div>
                                        <div
                                            class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                        >
                                            <div class="form-group mb-3">
                                                <label for="inputPwd"
                                                    >Password<span
                                                        v-if="errors.password"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    v-model="FormUser.password"
                                                    placeholder="Password"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="inputName"
                                                    >Họ và tên<span
                                                        v-if="errors.full_name"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="FormUser.full_name"
                                                    placeholder="Họ và tên"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="inputEmail"
                                                    >Email<span
                                                        v-if="errors.email"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <input
                                                    type="email"
                                                    class="form-control"
                                                    v-model="FormUser.email"
                                                    placeholder="Enter email"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="inputMaNhanVien"
                                                    >Mã nhân viên</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="
                                                        FormUser.ma_nhan_vien
                                                    "
                                                    placeholder="Mã nhân viên (ví dụ: 000123)"
                                                    maxlength="6"
                                                />
                                                <small
                                                    class="form-text text-muted"
                                                    >Nhập mã nhân viên dạng 6
                                                    chữ số (ví dụ:
                                                    000123)</small
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div
                                            class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                        >
                                            <div class="form-group mb-3">
                                                <label
                                                    for="inputPosition"
                                                    class="form-label"
                                                    >Chức vụ<span
                                                        v-if="errors.position"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <select
                                                    v-model="FormUser.position"
                                                    id="position"
                                                    class="form-control"
                                                >
                                                    <option value="" disabled>
                                                        Select Position
                                                    </option>
                                                    <option
                                                        v-for="position in positions"
                                                        :key="
                                                            position.id_position
                                                        "
                                                        :value="
                                                            position.id_position
                                                        "
                                                    >
                                                        {{ position.position }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div
                                            class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                        >
                                            <div class="form-group mb-3">
                                                <label
                                                    for="inputStation"
                                                    class="form-label"
                                                    >Trạm nông vụ<span
                                                        v-if="errors.station"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <select
                                                    v-model="FormUser.station"
                                                    id="inputStation"
                                                    class="form-control"
                                                >
                                                    <option
                                                        v-for="station in stations"
                                                        :key="station.id"
                                                        :value="
                                                            station.ma_don_vi
                                                        "
                                                    >
                                                        {{ station.Name }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="inputName"
                                                    >SĐT</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="FormUser.phone"
                                                    placeholder="Số điện thoại"
                                                />
                                            </div>
                                        </div>
                                        <div
                                            class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                        >
                                            <div class="form-group mb-3">
                                                <label for="inputRole"
                                                    >Role<span
                                                        v-if="errors.role_id"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <select
                                                    v-model="FormUser.role_id"
                                                    id="role_id"
                                                    class="form-control"
                                                >
                                                    <option value="" disabled>
                                                        Select Role
                                                    </option>
                                                    <option
                                                        v-for="role in roles"
                                                        :key="role.id"
                                                        :value="role.id"
                                                    >
                                                        {{ role.name }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div
                                            class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                        >
                                            <div class="form-group mb-3">
                                                <label for="inputStatus"
                                                    >Status<span
                                                        v-if="errors.status"
                                                        class="text-danger"
                                                        >*</span
                                                    ></label
                                                >
                                                <select
                                                    v-model="FormUser.status"
                                                    id="status"
                                                    class="form-control"
                                                >
                                                    <option value="active">
                                                        Active
                                                    </option>
                                                    <option value="inactive">
                                                        Inactive
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replace existing table section with this improved version -->
                <div v-else>
                    <div class="card">
                        <div class="card-body">
                            <!-- For Desktop: Table View -->
                            <div v-if="!isMobile" class="table-responsive">
                                <!-- Add reset filters button -->
                                <span
                                    class="reset-all-filters-btn"
                                    title="Reset all filters"
                                    @click="resetAllFilters"
                                >
                                    <i class="fas fa-redo-alt"></i>
                                </span>
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>
                                                Username
                                                <button
                                                    @click="
                                                        toggleFilter('username')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.username,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter ===
                                                        'username'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <input
                                                        type="text"
                                                        v-model="
                                                            columnFilters.username
                                                        "
                                                        class="form-control form-control-sm mb-2"
                                                        placeholder="Filter by username..."
                                                    />
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'username'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'username'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Full Name
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'full_name'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.full_name,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter ===
                                                        'full_name'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <input
                                                        type="text"
                                                        v-model="
                                                            columnFilters.full_name
                                                        "
                                                        class="form-control form-control-sm mb-2"
                                                        placeholder="Filter by name..."
                                                    />
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'full_name'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'full_name'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Mã NV
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'ma_nhan_vien'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.ma_nhan_vien,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter ===
                                                        'ma_nhan_vien'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <input
                                                        type="text"
                                                        v-model="
                                                            columnFilters.ma_nhan_vien
                                                        "
                                                        class="form-control form-control-sm mb-2"
                                                        placeholder="Filter by employee ID..."
                                                    />
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'ma_nhan_vien'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'ma_nhan_vien'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Chức vụ
                                                <button
                                                    @click="
                                                        toggleFilter('position')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues
                                                                    .position
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter ===
                                                        'position'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <div
                                                        class="max-h-40 overflow-y-auto"
                                                    >
                                                        <div
                                                            v-for="position in positions"
                                                            :key="
                                                                position.id_position
                                                            "
                                                            class="form-check"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input"
                                                                :id="
                                                                    'pos-' +
                                                                    position.id_position
                                                                "
                                                                :value="
                                                                    position.id_position
                                                                "
                                                                v-model="
                                                                    selectedFilterValues.position
                                                                "
                                                            />
                                                            <label
                                                                class="form-check-label"
                                                                :for="
                                                                    'pos-' +
                                                                    position.id_position
                                                                "
                                                            >
                                                                {{
                                                                    position.position
                                                                }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'position'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'position'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Trạm
                                                <button
                                                    @click="
                                                        toggleFilter('station')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues
                                                                    .station
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter ===
                                                        'station'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <div
                                                        class="max-h-40 overflow-y-auto"
                                                    >
                                                        <div
                                                            v-for="station in stations"
                                                            :key="
                                                                station.ma_don_vi
                                                            "
                                                            class="form-check"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input"
                                                                :id="
                                                                    'station-' +
                                                                    station.ma_don_vi
                                                                "
                                                                :value="
                                                                    station.ma_don_vi
                                                                "
                                                                v-model="
                                                                    selectedFilterValues.station
                                                                "
                                                            />
                                                            <label
                                                                class="form-check-label"
                                                                :for="
                                                                    'station-' +
                                                                    station.ma_don_vi
                                                                "
                                                            >
                                                                {{
                                                                    station.Name
                                                                }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'station'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'station'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Email
                                                <button
                                                    @click="
                                                        toggleFilter('email')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.email,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter === 'email'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <input
                                                        type="text"
                                                        v-model="
                                                            columnFilters.email
                                                        "
                                                        class="form-control form-control-sm mb-2"
                                                        placeholder="Filter by email..."
                                                    />
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'email'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'email'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Phone
                                                <button
                                                    @click="
                                                        toggleFilter('phone')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.phone,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter === 'phone'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <input
                                                        type="text"
                                                        v-model="
                                                            columnFilters.phone
                                                        "
                                                        class="form-control form-control-sm mb-2"
                                                        placeholder="Filter by phone..."
                                                    />
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'phone'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'phone'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Role
                                                <button
                                                    @click="
                                                        toggleFilter('role_id')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues
                                                                    .role_id
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter ===
                                                        'role_id'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <div
                                                        class="max-h-40 overflow-y-auto"
                                                    >
                                                        <div
                                                            v-for="role in roles"
                                                            :key="role.id"
                                                            class="form-check"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input"
                                                                :id="
                                                                    'role-' +
                                                                    role.id
                                                                "
                                                                :value="role.id"
                                                                v-model="
                                                                    selectedFilterValues.role_id
                                                                "
                                                            />
                                                            <label
                                                                class="form-check-label"
                                                                :for="
                                                                    'role-' +
                                                                    role.id
                                                                "
                                                            >
                                                                {{ role.name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'role_id'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'role_id'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                Status
                                                <button
                                                    @click="
                                                        toggleFilter('status')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues
                                                                    .status
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                                <div
                                                    v-if="
                                                        activeFilter ===
                                                        'status'
                                                    "
                                                    class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                >
                                                    <div
                                                        class="max-h-40 overflow-y-auto"
                                                    >
                                                        <div
                                                            v-for="status in uniqueValues.status"
                                                            :key="status"
                                                            class="form-check"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input"
                                                                :id="
                                                                    'status-' +
                                                                    status
                                                                "
                                                                :value="status"
                                                                v-model="
                                                                    selectedFilterValues.status
                                                                "
                                                            />
                                                            <label
                                                                class="form-check-label"
                                                                :for="
                                                                    'status-' +
                                                                    status
                                                                "
                                                            >
                                                                {{ status }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between mt-2"
                                                    >
                                                        <button
                                                            @click="
                                                                resetFilter(
                                                                    'status'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-outline-secondary"
                                                        >
                                                            Clear
                                                        </button>
                                                        <button
                                                            @click="
                                                                applyFilter(
                                                                    'status'
                                                                )
                                                            "
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            Apply
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                user, index
                                            ) in paginatedUsers.data"
                                            :key="user.id"
                                            class="desktop-row"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ user.username }}</td>
                                            <td>{{ user.full_name }}</td>
                                            <td>
                                                {{
                                                    formatEmployeeId(
                                                        user.ma_nhan_vien
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    getPositionName(
                                                        user.position
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    getStationName(user.station)
                                                }}
                                            </td>
                                            <td>{{ user.email }}</td>
                                            <td>{{ user.phone }}</td>
                                            <td
                                                :class="[
                                                    roleClasses(
                                                        getRoleName(
                                                            user.role_id
                                                        )
                                                    ),
                                                    'text-center',
                                                ]"
                                            >
                                                <i
                                                    :class="{
                                                        'bx bx-user':
                                                            getRoleName(
                                                                user.role_id
                                                            ) === 'User',
                                                        'bx bx-shield-quarter':
                                                            getRoleName(
                                                                user.role_id
                                                            ) === 'Admin',
                                                        'bx bx-briefcase-alt':
                                                            getRoleName(
                                                                user.role_id
                                                            ) === 'Manager',
                                                        'bx bx-crown':
                                                            getRoleName(
                                                                user.role_id
                                                            ) === 'Super Admin',
                                                    }"
                                                ></i>
                                                {{ getRoleName(user.role_id) }}
                                            </td>
                                            <td
                                                :class="{
                                                    'text-success':
                                                        user.status ===
                                                        'active',
                                                    'text-danger':
                                                        user.status ===
                                                        'inactive',
                                                }"
                                            >
                                                {{ user.status }}
                                            </td>
                                            <td
                                                class="text-center actions-column"
                                            >
                                                <div class="dropdown">
                                                    <button
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false"
                                                    >
                                                        <i
                                                            class="bx bx-dots-vertical-rounded"
                                                        ></i>
                                                    </button>
                                                    <!-- Existing dropdown menu -->
                                                    <ul
                                                        class="dropdown-menu"
                                                        v-if="
                                                            userHasPermission(
                                                                'update'
                                                            ) ||
                                                            userHasPermission(
                                                                'delete'
                                                            )
                                                        "
                                                    >
                                                        <li>
                                                            <a
                                                                v-if="
                                                                    userHasPermission(
                                                                        'update'
                                                                    )
                                                                "
                                                                class="dropdown-item"
                                                                href="#"
                                                                @click="
                                                                    EditUser(
                                                                        user.id
                                                                    )
                                                                "
                                                                ><i
                                                                    class="bx bx-edit-alt me-1"
                                                                ></i
                                                                >Edit</a
                                                            >
                                                        </li>
                                                        <li
                                                            v-if="
                                                                userHasPermission(
                                                                    'delete'
                                                                )
                                                            "
                                                        >
                                                            <a
                                                                class="dropdown-item"
                                                                href="#"
                                                                @click="
                                                                    DelUser(
                                                                        user.id
                                                                    )
                                                                "
                                                                ><i
                                                                    class="bx bx-trash me-1"
                                                                ></i
                                                                >Del</a
                                                            >
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- For Mobile: Card View -->
                            <div v-else class="card-container">
                                <div
                                    v-for="(user, index) in paginatedUsers.data"
                                    :key="user.id"
                                    class="user-card mb-3 p-3 rounded border"
                                >
                                    <div
                                        class="card-header d-flex justify-content-between align-items-center mb-2"
                                    >
                                        <div class="user-index fw-bold">
                                            #{{ index + 1 }}
                                        </div>
                                        <div class="dropdown">
                                            <button
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false"
                                            >
                                                <i
                                                    class="bx bx-dots-vertical-rounded"
                                                ></i>
                                            </button>
                                            <ul
                                                class="dropdown-menu"
                                                v-if="
                                                    userHasPermission(
                                                        'update'
                                                    ) ||
                                                    userHasPermission('delete')
                                                "
                                            >
                                                <li>
                                                    <a
                                                        v-if="
                                                            userHasPermission(
                                                                'update'
                                                            )
                                                        "
                                                        class="dropdown-item"
                                                        href="#"
                                                        @click="
                                                            EditUser(user.id)
                                                        "
                                                        ><i
                                                            class="bx bx-edit-alt me-1"
                                                        ></i
                                                        >Edit</a
                                                    >
                                                </li>
                                                <li
                                                    v-if="
                                                        userHasPermission(
                                                            'delete'
                                                        )
                                                    "
                                                >
                                                    <a
                                                        class="dropdown-item"
                                                        href="#"
                                                        @click="
                                                            DelUser(user.id)
                                                        "
                                                        ><i
                                                            class="bx bx-trash me-1"
                                                        ></i
                                                        >Del</a
                                                    >
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="user-info">
                                        <div class="info-item">
                                            <strong>Username:</strong>
                                            {{ user.username }}
                                        </div>
                                        <div class="info-item">
                                            <strong>Họ và tên:</strong>
                                            {{ user.full_name }}
                                        </div>
                                        <div class="info-item">
                                            <strong>Mã NV:</strong>
                                            {{
                                                formatEmployeeId(
                                                    user.ma_nhan_vien
                                                )
                                            }}
                                        </div>
                                        <div class="info-item">
                                            <strong>Chức vụ:</strong>
                                            {{ getPositionName(user.position) }}
                                        </div>
                                        <div class="info-item">
                                            <strong>Trạm:</strong>
                                            {{ getStationName(user.station) }}
                                        </div>
                                        <div class="info-item">
                                            <strong>Email:</strong>
                                            {{ user.email || "N/A" }}
                                        </div>
                                        <div class="info-item">
                                            <strong>Phone:</strong>
                                            {{ user.phone || "N/A" }}
                                        </div>
                                    </div>

                                    <div
                                        class="status-section d-flex justify-content-between align-items-center mt-3"
                                    >
                                        <div
                                            :class="[
                                                roleClasses(
                                                    getRoleName(user.role_id)
                                                ),
                                                'role-badge',
                                            ]"
                                        >
                                            <i
                                                :class="{
                                                    'bx bx-user':
                                                        getRoleName(
                                                            user.role_id
                                                        ) === 'User',
                                                    'bx bx-shield-quarter':
                                                        getRoleName(
                                                            user.role_id
                                                        ) === 'Admin',
                                                    'bx bx-briefcase-alt':
                                                        getRoleName(
                                                            user.role_id
                                                        ) === 'Manager',
                                                    'bx bx-crown':
                                                        getRoleName(
                                                            user.role_id
                                                        ) === 'Super Admin',
                                                }"
                                            ></i>
                                            {{ getRoleName(user.role_id) }}
                                        </div>
                                        <div
                                            class="status-badge"
                                            :class="{
                                                'active-status':
                                                    user.status === 'active',
                                                'inactive-status':
                                                    user.status === 'inactive',
                                            }"
                                        >
                                            {{ user.status }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Keep pagination section -->
                            <div class="flex justify-center mt-4">
                                <pagination
                                    :data="paginatedUsers"
                                    @pagination-change-page="pageChanged"
                                    :limit="5"
                                    :classes="paginationClasses"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- {{ FormUser }} -->
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { Bootstrap5Pagination } from "laravel-vue-pagination";

export default {
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    components: {
        Pagination: Bootstrap5Pagination,
    },

    data() {
        return {
            search: "",
            currentPage: 1,
            perPage: 10,
            ShowFormUser: false,
            users: [],
            positions: [],
            stations: [],
            roles: [],
            userPermissions: [],
            paginationClasses: {
                ul: "pagination rounded success justify-content-center",
                li: "page-item",
                a: "page-link",
                active: "active",
                disabled: "disabled",
            },
            FormUser: {
                username: "",
                password: "",
                full_name: "",
                email: "",
                phone: "",
                position: "",
                station: "",
                role_id: null,
                status: "active",
                ma_nhan_vien: null,
            },
            FormType: true,
            EditID: "",
            isLoading: false,
            // New filter properties
            activeFilter: null,
            columnFilters: {
                username: "",
                full_name: "",
                ma_nhan_vien: "",
                email: "",
                phone: "",
            },
            uniqueValues: {
                position: [],
                station: [],
                role_id: [],
                status: ["active", "inactive"],
            },
            selectedFilterValues: {
                position: [],
                station: [],
                role_id: [],
                status: [],
            },
            // Add this to your data() return object
            isMobile: window.innerWidth < 768,
        };
    },
    //สร้างการตรวจสอบฟอร์มใส่ลูกเล่นเมื่อผู้ใช้กรอกข้อมูลไม่ครบหรือเป็นถ้าว่างให้ขึ้นข้อความแสดงใน input นั้น
    computed: {
        // Add filtered users computed property
        filteredUsers() {
            return this.users.filter((user) => {
                // Global search
                const searchTerm = this.search.toLowerCase();
                const matchesGlobalSearch =
                    user.username.toLowerCase().includes(searchTerm) ||
                    user.full_name.toLowerCase().includes(searchTerm) ||
                    (user.email &&
                        user.email.toLowerCase().includes(searchTerm)) ||
                    (user.phone &&
                        user.phone.toLowerCase().includes(searchTerm)) ||
                    this.getStationName(user.station)
                        .toLowerCase()
                        .includes(searchTerm) ||
                    this.getPositionName(user.position)
                        .toLowerCase()
                        .includes(searchTerm) ||
                    this.getRoleName(user.role_id)
                        .toLowerCase()
                        .includes(searchTerm);

                // Column specific filters
                const matchesColumnFilters =
                    // Text-based filters
                    (!this.columnFilters.username ||
                        user.username
                            .toLowerCase()
                            .includes(
                                this.columnFilters.username.toLowerCase()
                            )) &&
                    (!this.columnFilters.full_name ||
                        user.full_name
                            .toLowerCase()
                            .includes(
                                this.columnFilters.full_name.toLowerCase()
                            )) &&
                    (!this.columnFilters.ma_nhan_vien ||
                        (user.ma_nhan_vien &&
                            this.formatEmployeeId(user.ma_nhan_vien).includes(
                                this.columnFilters.ma_nhan_vien
                            ))) &&
                    (!this.columnFilters.email ||
                        (user.email &&
                            user.email
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.email.toLowerCase()
                                ))) &&
                    (!this.columnFilters.phone ||
                        (user.phone &&
                            user.phone
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.phone.toLowerCase()
                                ))) &&
                    // Dropdown filters
                    (this.selectedFilterValues.position.length === 0 ||
                        this.selectedFilterValues.position.includes(
                            user.position
                        )) &&
                    (this.selectedFilterValues.station.length === 0 ||
                        this.selectedFilterValues.station.includes(
                            user.station
                        )) &&
                    (this.selectedFilterValues.role_id.length === 0 ||
                        this.selectedFilterValues.role_id.includes(
                            user.role_id
                        )) &&
                    (this.selectedFilterValues.status.length === 0 ||
                        this.selectedFilterValues.status.includes(user.status));

                return matchesGlobalSearch && matchesColumnFilters;
            });
        },
        // Add paginated users computed property
        paginatedUsers() {
            const total = this.filteredUsers.length;
            const lastPage = Math.ceil(total / this.perPage) || 1;
            const start = (this.currentPage - 1) * this.perPage;
            const data = this.filteredUsers.slice(start, start + this.perPage);

            return {
                current_page: this.currentPage,
                data,
                first_page_url: "?page=1",
                from: total > 0 ? start + 1 : 0,
                last_page: lastPage,
                last_page_url: `?page=${lastPage}`,
                next_page_url:
                    this.currentPage < lastPage
                        ? `?page=${this.currentPage + 1}`
                        : null,
                path: "",
                per_page: this.perPage,
                prev_page_url:
                    this.currentPage > 1
                        ? `?page=${this.currentPage - 1}`
                        : null,
                to: start + data.length,
                total,
            };
        },
        errors() {
            const errors = {};
            if (!this.FormUser.username) {
                errors.username = "Username is required";
            }
            if (this.FormType && !this.FormUser.password) {
                errors.password = "Password is required";
            }
            if (!this.FormUser.full_name) {
                errors.full_name = "Full name is required";
            }
            // if (!this.FormUser  .email) {
            //   errors.email = 'Email is required';
            // }
            // if (!this.FormUser  .phone) {
            //   errors.phone is required';
            // }
            if (!this.FormUser.position) {
                errors.position = "Position is required";
            }
            if (!this.FormUser.station) {
                errors.station = "Station is required";
            }
            if (!this.FormUser.role_id) {
                errors.role_id = "Role is required";
            }
            if (!this.FormUser.status) {
                errors.status = "Status is required";
            }
            return errors;
        },
        FormValid() {
            return Object.keys(this.errors).length > 0;
        },
        roleClasses() {
            return (roleName) => {
                switch (roleName) {
                    case "User":
                        return "badge bg-label-primary";
                    case "Admin":
                        return "badge bg-label-success";
                    case "Manager":
                        return "badge bg-label-warning";
                    case "Super Admin":
                        return "badge bg-label-danger";
                    default:
                        return "";
                }
            };
        },
    },

    mounted() {
        this.fetchRoles(); // เรียกใช้งานฟังก์ชัน fetchRoles
        window.addEventListener("resize", this.handleResize);
    },
    created() {
        this.fetchUsers();
        this.fetchPositions();
        this.fetchStations();
        this.fetchRoles();
        this.fetchUserPermissions(); // เรียกอ่าน permission เมื่อสร้าง component
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.handleResize);
    },
    methods: {
        // Add new pagination method
        pageChanged(page) {
            this.currentPage = page;
        },
        // เพิ่มเมธอดสำหรับแสดงชื่อตำแหน่ง
        getPositionName(positionId) {
            const position = this.positions.find(
                (p) => p.id_position === positionId
            );
            return position ? position.position : positionId;
        },

        // ฟังก์ชันสำหรับ fetch data permissionName ของผู้ใช้
        fetchUserPermissions() {
            axios
                .get("/api/user/permissions", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    this.userPermissions = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching user permissions:", error);
                });
        },
        userHasPermission(permissionName) {
            return this.userPermissions.includes(permissionName);
        },

        //add user
        Adduser() {
            this.ShowFormUser = true;
            this.FormType = true; // กำหนดค่าให้ FormType เป็น true เพื่อบอกว่าเป็นการเพิ่ม
            this.FormUser = {
                username: "",
                password: "",
                full_name: "",
                email: "",
                phone: "",
                position: "",
                station: "",
                role_id: null,
                status: "active",
                ma_nhan_vien: null,
            };
        },
        //Edit user From headers:{ Authorization: 'Bearer '+this.store.getToken }
        EditUser(id) {
            this.FormType = false; // กำหนดค่าให้ FormType เป็น false เพื่อบอกว่าเป็นการแก้ไข
            this.EditID = id;
            console.log(id);

            axios
                .get(`/api/user/edit/${id}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((res) => {
                    this.FormUser = res.data;
                    this.FormUser.password = ""; // เคลียร์ฟิลด์รหัสผ่านเริ่มต้น
                    this.ShowFormUser = true;
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response && err.response.status === 401) {
                        localStorage.removeItem("web_token");
                        localStorage.removeItem("web_user");
                        this.store.logout();
                        this.$router.push("/login");
                    }
                });
        },

        //Save add new user
        Saveuser() {
            this.isLoading = true; // เริ่มการแสดงผล loading
            if (this.FormType) {
                axios
                    .post("/api/user/add", this.FormUser, {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    })
                    .then((res) => {
                        this.isLoading = false; // หยุดการแสดงผล loading
                        if (res.data.message === "success") {
                            this.ShowFormUser = false;
                            this.fetchUsers();
                            // แสดงข้อความแจ้งเตือนบักทึกสำเร็จ
                            this.$swal({
                                toast: true,
                                position: "top-end",
                                icon: "success",
                                title: "Lưu thanh công!",
                                // title: res.data.message,
                                showConfirmButton: false,
                                timer: 1500,
                            });

                            this.FormUser = {
                                username: "",
                                password: "",
                                full_name: "",
                                email: "",
                                phone: "",
                                position: "",
                                station: "",
                                role_id: null,
                                status: "active",
                                ma_nhan_vien: null,
                            };
                        } else {
                            // แสดงข้อความแจ้งเตือนบักทึกไม่สำเร็จ
                            this.$swal({
                                icon: "error",
                                title: "Lỗi!",
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 3500,
                            });
                        }
                    })
                    .catch((err) => {
                        this.isLoading = false; // หยุดการแสดงผล loading
                        console.log(err);
                        if (err.response) {
                            if (err.response.status == 401) {
                                // clear localstorage
                                localStorage.removeItem("web_token");
                                localStorage.removeItem("web_user");

                                // clear store
                                this.store.logout();

                                // redirect to login
                                this.$router.push("/login");
                            }
                        }
                    });
            } else {
                const updateData = { ...this.FormUser };
                if (!updateData.password) {
                    delete updateData.password; // ลบฟิลด์รหัสผ่านถ้ามันว่าง
                }
                axios
                    .put(`/api/user/update/${this.EditID}`, this.FormUser, {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    })
                    .then((res) => {
                        this.isLoading = false; // หยุดการแสดงผล loading
                        if (res.data.message === "success") {
                            this.fetchUsers();
                            this.ShowFormUser = false;
                            // แสดงข้อความแจ้งเตือนบักทึกสำเร็จ
                            this.$swal({
                                toast: true,
                                position: "top-end",
                                icon: "success",
                                title: "Lưu thanh công!",
                                // title: res.data.message,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                            this.FormUser = {
                                username: "",
                                password: "",
                                full_name: "",
                                email: "",
                                phone: "",
                                position: "",
                                station: "",
                                role_id: null,
                                status: "active",
                                ma_nhan_vien: null,
                            };
                        } else {
                            // แสดงข้อความแจ้งเตือนบักทึกไม่สำเร็จ
                            this.$swal({
                                icon: "error",
                                title: "Lỗi!",
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 3500,
                            });
                        }
                    })
                    .catch((err) => {
                        this.isLoading = false; // หยุดการแสดงผล loading
                        console.log(err);
                        if (err.response) {
                            if (err.response.status == 401) {
                                // clear localstorage
                                localStorage.removeItem("web_token");
                                localStorage.removeItem("web_user");
                                // clear store
                                this.store.logout();
                                // redirect to login
                                this.$router.push("/login");
                            }
                        }
                    });
            }
        },
        //Delete user
        DelUser(id) {
            this.$swal({
                title: "Bạn muốn xóa?",
                text: "Bạn có chắc chắn muốn xóa dữ liệu này!",
                icon: "warning",
                showCancelButton: true,

                confirmButtonText: "Ok",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-success",
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    axios
                        .delete(`/api/user/delete/${id}`, {
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        })
                        .then((res) => {
                            if (res.data.message === "success") {
                                this.fetchUsers();
                                this.ShowFormUser = false;
                                // แสดงข้อความแจ้งเตือนลบสำเร็จ
                                this.$swal({
                                    title: "Đã xóa thanh công!",
                                    // text: res.data.message,
                                    icon: "success",
                                    showConfirmButton: false,
                                    timer: 2500,
                                });
                            } else {
                                // แสดงข้อความแจ้งเตือนลบไม่สำเร็จ
                                this.$swal({
                                    title: "Không xóa được!",
                                    text: res.data.message,
                                    icon: "error",
                                });
                            }
                        })
                        .catch((err) => {
                            console.log(err);
                            if (err.response) {
                                if (err.response.status == 401) {
                                    // clear localstorage
                                    localStorage.removeItem("web_token");
                                    localStorage.removeItem("web_user");
                                    // clear store
                                    this.store.logout();
                                    // redirect to login
                                    this.$router.push("/login");
                                }
                            }
                        });
                }
            });
        },
        //cannel user
        Canneluser() {
            this.ShowFormUser = false;
            this.fetchUsers();
        },

        fetchUsers() {
            axios
                .get("/api/users", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((res) => {
                    this.users = res.data;
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response) {
                        if (err.response.status == 401) {
                            // clear localstorage
                            localStorage.removeItem("web_token");
                            localStorage.removeItem("web_user");

                            // clear store
                            this.store.logout();

                            // redirect to login
                            this.$router.push("/login");
                        }
                    }
                });
        },
        fetchPositions() {
            axios
                .get("/api/positions", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    this.positions = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching positions:", error);
                });
        },
        getStationName(stationCode) {
            const station = this.stations.find(
                (s) => s.ma_don_vi === stationCode
            );
            return station ? station.Name : stationCode;
        },
        fetchStations() {
            axios
                .get("/api/stations", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    this.stations = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching stations:", error);
                });
        },
        fetchRoles() {
            axios
                .get("/api/roles", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    this.roles = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching roles:", error);
                });
        },
        getRoleName(roleId) {
            const role = this.roles.find((role) => role.id === roleId);
            return role ? role.name : "Unknown";
        },
        formatEmployeeId(value) {
            if (!value) return "";
            // Ensure the value is treated as a string and pad with leading zeros to 6 digits
            return String(value).padStart(6, "0");
        },
        // Add these new filter methods
        toggleFilter(column) {
            this.activeFilter = this.activeFilter === column ? null : column;

            // If opening a dropdown filter, ensure unique values are populated
            if (
                ["position", "station", "role_id"].includes(column) &&
                this.activeFilter === column
            ) {
                this.updateUniqueValues(column);
            }
        },

        updateUniqueValues(column) {
            if (column === "position") {
                this.uniqueValues.position = this.positions;
            } else if (column === "station") {
                this.uniqueValues.station = this.stations;
            } else if (column === "role_id") {
                this.uniqueValues.role_id = this.roles;
            }
        },

        resetFilter(column) {
            if (["position", "station", "role_id", "status"].includes(column)) {
                this.selectedFilterValues[column] = [];
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
        },

        applyFilter(column) {
            this.activeFilter = null;
            this.currentPage = 1;
        },

        resetAllFilters() {
            // Reset global search
            this.search = "";

            // Reset all column filters
            Object.keys(this.columnFilters).forEach((key) => {
                this.columnFilters[key] = "";
            });

            // Reset all dropdown filters
            Object.keys(this.selectedFilterValues).forEach((key) => {
                this.selectedFilterValues[key] = [];
            });

            // Reset active filter
            this.activeFilter = null;

            // Reset to first page
            this.currentPage = 1;
        },

        // Existing methods remain unchanged...
        handleResize() {
            this.isMobile = window.innerWidth < 768;
        },
    },
};
</script>

<style scoped>
.table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.text-danger {
    color: #dc3545;
    font-size: 14px;
    font-weight: bold;
    vertical-align: middle;
}
.text-success {
    color: #28a745;
    font-size: 14px;
    font-weight: bold;
    vertical-align: middle;
}

.badge {
    display: inline-block;
    padding: 0.25em 0.4em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
    margin: auto; /* Center horizontally */
}
.bg-label-primary {
    color: #fff;
    background-color: #007bff;
}
.bg-label-success {
    color: #fff;
    background-color: #28a745;
}
.bg-label-warning {
    color: #212529;
    background-color: #ffc107;
}
.bg-label-danger {
    color: #fff;
    background-color: #dc3545;
}
.table td.text-center {
    display: flex;
    align-items: center;
    justify-content: center;
}
/* ยกเว้นการตัดเนื้อหาและชอบเนื้อหาในคอลัมน์ Actions */
.table td.actions-column {
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
}

.search-input {
    min-width: 250px;
    height: 38px;
    transition: box-shadow 0.3s ease;
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    border-color: #10b981;
}

/* Replace the existing .badge styles and other styles... */

/* Mobile responsive styles */
@media (max-width: 768px) {
    .d-flex.flex-row {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .search-container {
        max-width: none;
        margin-top: 1rem;
    }

    .button-30-save {
        width: 100%;
        justify-content: center;
    }
}

/* Add new filter-related styles */
.table th {
    position: relative;
    white-space: nowrap;
    min-width: 150px;
}

.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-left: 5px;
    font-size: 0.75rem;
    transition: color 0.2s;
}

.filter-btn:hover .fas:not(.text-green-500) {
    color: #10b981;
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    min-width: 250px;
    max-width: 300px;
    z-index: 1050;
    background-color: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -4px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.max-h-40.overflow-y-auto {
    max-height: 160px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.max-h-40.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.max-h-40.overflow-y-auto::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.max-h-40.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10:before {
    content: "";
    position: absolute;
    top: -6px;
    left: 10px;
    width: 12px;
    height: 12px;
    background: white;
    transform: rotate(45deg);
    border-left: 1px solid #e2e8f0;
    border-top: 1px solid #e2e8f0;
    z-index: -1;
}

.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 35px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: #198754;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.reset-all-filters-btn:hover {
    background: #10b981;
    transform: rotate(30deg);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Make sure the card has proper overflow handling */
.card-body {
    position: relative;
    overflow: visible;
    padding: 1.5rem;
}

/* Table update */
.table {
    margin-bottom: 0;
    width: 100%;
}

/* Make table cells more clean looking */
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 0.75rem;
    vertical-align: middle;
    border-color: #dee2e6;
}

.table td {
    padding: 0.625rem 0.75rem;
    vertical-align: middle;
    border-color: #dee2e6;
    transition: background-color 0.15s;
}

/* Row hover effect */
.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Improved search input */
.search-container {
    max-width: 350px;
    width: 100%;
}

.search-input {
    height: 38px;
    transition: all 0.3s ease;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    border-color: #10b981;
}

.input-group-text {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

/* Modern button styling */
.btn-success {
    background-color: #10b981;
    border-color: #10b981;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    transition: all 0.2s ease;
}

.btn-success:hover {
    background-color: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

.btn-success:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .d-flex.flex-row {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .search-container {
        max-width: none;
        margin-top: 1rem;
    }

    .btn-success {
        width: 100%;
        justify-content: center;
    }
}

/* Table update with improved spacing */
.table {
    margin-bottom: 0;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 0.85rem 0.75rem;
    vertical-align: middle;
    border-color: #dee2e6;
    position: relative;
}

.table td {
    padding: 0.625rem 0.75rem;
    vertical-align: middle;
    border-color: #dee2e6;
    transition: background-color 0.15s;
}

/* Row hover effect */
.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Card improvements */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: none;
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.card-body {
    position: relative;
    overflow: visible;
    padding: 1.5rem;
}

.search-container {
    max-width: 350px;
    width: 100%;
}

.search-input {
    height: 38px;
    transition: all 0.3s ease;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    border-color: #10b981;
}

@media (max-width: 768px) {
    .d-flex.flex-row {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .search-container {
        max-width: none;
        margin-top: 1rem;
    }

    .button-30-save {
        width: 100%;
        justify-content: center;
    }
}

/* Mobile layout styles */
@media (max-width: 768px) {
    .d-flex.flex-row {
        flex-direction: row !important; /* Keep as row for mobile */
        align-items: center !important;
        gap: 10px;
    }

    .search-container {
        max-width: none;
        width: 70%; /* Take 70% of width on mobile */
        margin-top: 0;
    }

    .button-container {
        width: 30%; /* Take 30% of width on mobile */
    }

    .button-30-save {
        width: 100%;
        justify-content: center;
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }

    /* Make the icon a bit smaller on mobile to fit better */
    .button-30-save i {
        font-size: 0.85rem;
    }

    /* Ensure the search box fits properly */
    .search-input {
        height: 38px;
        min-width: 0;
    }
}

/* Keep your existing styles and add these new styles */

/* Mobile card view styles */
.card-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.user-card {
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    border: 1px solid rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.user-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.2);
    border-color: rgba(16, 185, 129, 0.3);
}

.card-header {
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-top: 0.5rem;
}

.info-item {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.info-item strong {
    min-width: 100px;
    color: #6c757d;
}

.role-badge {
    padding: 0.35em 0.65em;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.status-badge {
    padding: 0.35em 0.65em;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 0.375rem;
}

.active-status {
    background-color: rgba(40, 167, 69, 0.15);
    color: #28a745;
}

.inactive-status {
    background-color: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}

/* Desktop row styles */
.desktop-row {
    transition: background-color 0.2s ease, transform 0.2s ease,
        box-shadow 0.2s ease;
    cursor: pointer;
}

.desktop-row:hover {
    background-color: rgba(16, 185, 129, 0.05);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Filter button and dropdowns */
.table th {
    position: relative;
    white-space: nowrap;
    min-width: 120px;
}

.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-left: 5px;
    font-size: 0.75rem;
    transition: color 0.2s;
}

.filter-btn:hover .fas:not(.text-green-500) {
    color: #10b981;
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    min-width: 250px;
    max-width: 300px;
    z-index: 1050;
    background-color: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -4px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 30px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: #198754;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.reset-all-filters-btn:hover {
    background: #10b981;
    transform: rotate(30deg);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Improve table appearance */
.table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 1rem;
}

.table {
    margin-bottom: 0;
    width: 100%;
    min-width: 1200px; /* ปรับค่านี้ตามจำนวนคอลัมน์ */
    border-collapse: separate;
    border-spacing: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 0.85rem 0.75rem;
    vertical-align: middle;
    border-color: #dee2e6;
    position: relative;
    white-space: nowrap;
}

.table td {
    padding: 0.625rem 0.75rem;
    vertical-align: middle;
    border-color: #dee2e6;
    transition: background-color 0.15s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Enhanced pagination styles */
.pagination {
    display: flex;
    list-style: none;
    padding-left: 0;
    margin: 1rem 0;
    border-radius: 0.25rem;
}

.pagination li {
    margin: 0 0.25rem;
}

.pagination li a {
    color: #495057;
    position: relative;
    display: block;
    padding: 0.5rem 0.75rem;
    line-height: 1.25;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    transition: all 0.2s ease-in-out;
}

.pagination li.active a {
    z-index: 1;
    color: #fff;
    background-color: #10b981;
    border-color: #10b981;
}

.pagination li a:hover:not(.active) {
    color: #10b981;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

/* Loading styles */
#loading-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-container {
        max-width: none;
        width: 100%;
    }

    .button-container {
        width: 100%;
    }

    .button-30-save {
        width: 100%;
        justify-content: center;
    }

    .table th,
    .table td {
        min-width: auto;
    }

    .pagination li a {
        padding: 0.4rem 0.6rem;
    }

    /* CSS สำหรับมือถือคงเดิม */

    /* ตารางบนมือถือควรมีการปรับแต่ง */
    .table-responsive {
        border: 0; /* ลบเส้นขอบ */
        margin-bottom: 0;
    }
}
</style>
