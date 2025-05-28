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
                    <!-- For Desktop: Table View -->
                    <div v-if="!isMobile" class="table-container">
                        <!-- Add reset filters button -->
                        <span
                            class="reset-all-filters-btn"
                            title="Reset all filters"
                            @click="resetAllFilters"
                        >
                            <i class="fas fa-redo-alt"></i>
                        </span>
                        <div
                            class="table-scroll-container"
                            ref="tableScrollContainer"
                        >
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <!-- Keep your existing table headers here -->
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
                                            <!-- Rest of your header code -->
                                        </th>
                                        <th>
                                            Full Name
                                            <button
                                                @click="
                                                    toggleFilter('full_name')
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
                                                    activeFilter === 'full_name'
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
                                                    toggleFilter('ma_nhan_vien')
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
                                                    activeFilter === 'position'
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
                                                @click="toggleFilter('station')"
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
                                                    activeFilter === 'station'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="station in stations"
                                                        :key="station.ma_don_vi"
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
                                                            {{ station.Name }}
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
                                                @click="toggleFilter('email')"
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
                                                v-if="activeFilter === 'email'"
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
                                                            resetFilter('email')
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('email')
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
                                                @click="toggleFilter('phone')"
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
                                                v-if="activeFilter === 'phone'"
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
                                                            resetFilter('phone')
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('phone')
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
                                                @click="toggleFilter('role_id')"
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
                                                    activeFilter === 'role_id'
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
                                                @click="toggleFilter('status')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues
                                                                .status.length >
                                                            0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'status'"
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
                                            {{ getPositionName(user.position) }}
                                        </td>
                                        <td>
                                            {{ getStationName(user.station) }}
                                        </td>
                                        <td>{{ user.email }}</td>
                                        <td>{{ user.phone }}</td>
                                        <td
                                            :class="[
                                                roleClasses(
                                                    getRoleName(user.role_id)
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
                                                    user.status === 'active',
                                                'text-danger':
                                                    user.status === 'inactive',
                                            }"
                                        >
                                            {{ user.status }}
                                        </td>
                                        <td class="text-center actions-column">
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
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-card">
                            <pagination
                                :data="paginatedUsers"
                                @pagination-change-page="pageChanged"
                                :limit="5"
                                :classes="paginationClasses"
                            />
                        </div>
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
                                            userHasPermission('update') ||
                                            userHasPermission('delete')
                                        "
                                    >
                                        <li>
                                            <a
                                                v-if="
                                                    userHasPermission('update')
                                                "
                                                class="dropdown-item"
                                                href="#"
                                                @click="EditUser(user.id)"
                                                ><i
                                                    class="bx bx-edit-alt me-1"
                                                ></i
                                                >Edit</a
                                            >
                                        </li>
                                        <li v-if="userHasPermission('delete')">
                                            <a
                                                class="dropdown-item"
                                                href="#"
                                                @click="DelUser(user.id)"
                                                ><i class="bx bx-trash me-1"></i
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
                                    {{ formatEmployeeId(user.ma_nhan_vien) }}
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
                                        roleClasses(getRoleName(user.role_id)),
                                        'role-badge',
                                    ]"
                                >
                                    <i
                                        :class="{
                                            'bx bx-user':
                                                getRoleName(user.role_id) ===
                                                'User',
                                            'bx bx-shield-quarter':
                                                getRoleName(user.role_id) ===
                                                'Admin',
                                            'bx bx-briefcase-alt':
                                                getRoleName(user.role_id) ===
                                                'Manager',
                                            'bx bx-crown':
                                                getRoleName(user.role_id) ===
                                                'Super Admin',
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
    <!-- {{ FormUser }} -->
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { Bootstrap5Pagination } from "laravel-vue-pagination";

import PerfectScrollbar from "perfect-scrollbar";
import "perfect-scrollbar/css/perfect-scrollbar.css";

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
                ul: "pagination rounded-pill shadow-sm justify-content-center align-items-center",
                li: "page-item mx-1",
                a: "page-link modern-page-link",
                active: "active modern-active",
                disabled: "disabled modern-disabled",
            },
            ps: null,
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
        // Initialize PerfectScrollbar
        this.initPerfectScrollbar();
    },
    updated() {
        // Update scrollbar after component updates
        this.updateScrollbar();
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
        if (this.ps) {
            this.ps.destroy();
            this.ps = null;
        }
    },
    methods: {
        initPerfectScrollbar() {
            // Initialize PerfectScrollbar after the DOM is updated
            this.$nextTick(() => {
                if (this.$refs.tableScrollContainer) {
                    // Destroy existing instance if it exists
                    if (this.ps) {
                        this.ps.destroy();
                    }
                    // Create new PerfectScrollbar instance
                    this.ps = new PerfectScrollbar(
                        this.$refs.tableScrollContainer,
                        {
                            suppressScrollX: false,
                            wheelPropagation: false,
                        }
                    );
                }
            });
        },

        updateScrollbar() {
            // Update the scrollbar when data changes
            this.$nextTick(() => {
                if (this.ps) {
                    this.ps.update();
                }
            });
        },
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
            this.$router.push("/AddUser");
            // this.ShowFormUser = true;
            // this.FormType = true; // กำหนดค่าให้ FormType เป็น true เพื่อบอกว่าเป็นการเพิ่ม
            // this.FormUser = {
            //     username: "",
            //     password: "",
            //     full_name: "",
            //     email: "",
            //     phone: "",
            //     position: "",
            //     station: "",
            //     role_id: null,
            //     status: "active",
            //     ma_nhan_vien: null,
            // };
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
    watch: {
        search() {
            this.currentPage = 1;
            this.updateScrollbar();
        },
        paginatedUsers: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
        filteredUsers: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
    },
};
</script>

<style scoped>
/* Enhanced table styling for modern look */
.table-container {
    position: relative;
    width: 100%;
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.table-scroll-container {
    min-height: 410px;
    position: relative;
    max-height: calc(100vh - 240px);
    overflow: auto;
    border-radius: 0.75rem;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
}

/* Modern table styling without borders */
.table {
    margin-bottom: 0;
    width: 100%;
    min-width: 1200px;
    border-collapse: separate;
    border-spacing: 0;
    border: none;
    background-color: transparent;
}

/* Enhanced header styling */
.table thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

.table thead th {
    position: sticky;
    top: 0;
    background: #f8fafc;
    z-index: 20;
    padding: 1.25rem 1rem;
    vertical-align: middle;
    border: none;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.875rem;
    color: #059669;
    text-transform: none;
    letter-spacing: normal;
    white-space: nowrap;
    min-width: 120px;
    position: relative;
    transition: all 0.2s ease;
}

/* Add subtle separator between headers */
.table thead th:not(:last-child):after {
    content: "";
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background: linear-gradient(
        180deg,
        transparent 0%,
        #d1d5db 50%,
        transparent 100%
    );
}

/* Modern table body styling */
.table tbody {
    background-color: transparent;
}

.table td {
    padding: 0.5rem 0.5rem;
    vertical-align: middle;
    border: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.875rem;
    color: #374151;
    background-color: transparent;
}

/* Enhanced row styling with subtle separation */
.table tbody tr {
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 0.5rem;
    margin-bottom: 0.25rem;
}

/* Add subtle row separator */
.table tbody tr:not(:last-child):after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 1rem;
    right: 1rem;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(229, 231, 235, 0.6) 10%,
        rgba(229, 231, 235, 0.6) 90%,
        transparent 100%
    );
}

/* Modern hover effect */
.table tbody tr:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.05) 0%,
        rgba(16, 185, 129, 0.02) 100%
    );
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
    border-radius: 0.75rem;
}

.table tbody tr:hover:after {
    opacity: 0;
}

.table tbody tr:hover td {
    color: #065f46;
}

/* Enhanced zebra striping */
.table tbody tr:nth-child(even) {
    background: linear-gradient(
        135deg,
        rgba(248, 250, 252, 0.7) 0%,
        rgba(241, 245, 249, 0.3) 100%
    );
}

.table tbody tr:nth-child(odd) {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.9) 0%,
        rgba(248, 250, 252, 0.5) 100%
    );
}

/* Enhanced badges and status styling */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375em 0.75em;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.5rem;
    margin: auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

.bg-label-primary {
    color: #1e40af;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border: 1px solid rgba(30, 64, 175, 0.2);
}

.bg-label-success {
    color: #065f46;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 1px solid rgba(6, 95, 70, 0.2);
}

.bg-label-warning {
    color: #92400e;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid rgba(146, 64, 14, 0.2);
}

.bg-label-danger {
    color: #991b1b;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 1px solid rgba(153, 27, 27, 0.2);
}

/* Enhanced status styling */
.text-success {
    color: #059669;
    font-weight: 600;
}

.text-danger {
    color: #dc2626;
    font-weight: 600;
}

/* Enhanced action button styling */
.actions-column {
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
}

.dropdown-toggle {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 1px solid rgba(148, 163, 184, 0.3);
    border-radius: 0.5rem;
    padding: 0.5rem;
    transition: all 0.2s ease;
    color: #64748b;
}

.dropdown-toggle:hover {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
}

/* Enhanced filter button */
.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.375rem;
    margin-left: 0.375rem;
    color: #9ca3af;
    transition: all 0.2s ease;
    border-radius: 0.375rem;
}

.filter-btn:hover {
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    transform: scale(1.1);
}

.text-green-500 {
    color: #10b981;
}

/* Enhanced reset filters button */
.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 30px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.reset-all-filters-btn:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
}

/* Enhanced filter dropdown */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 280px;
    max-width: 320px;
    z-index: 1050;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    padding: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(226, 232, 240, 0.8);
    backdrop-filter: blur(8px);
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10:before {
    content: "";
    position: absolute;
    top: -8px;
    left: 15px;
    width: 16px;
    height: 16px;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    transform: rotate(45deg);
    border-left: 1px solid rgba(226, 232, 240, 0.8);
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    z-index: -1;
}

/* Enhanced row number column */
.table th:first-child,
.table td:first-child {
    width: 60px;
    min-width: 60px;
    max-width: 60px;
    text-align: center;
    font-weight: 600;
    color: #6b7280;
}

/* Perfect scrollbar modern styling */
.ps__rail-y {
    width: 8px;
    background-color: transparent !important;
    border-radius: 4px;
}

.ps__thumb-y {
    width: 6px;
    background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 4px;
    transition: all 0.2s ease;
}

.ps__rail-y:hover > .ps__thumb-y,
.ps__rail-y:focus > .ps__thumb-y {
    width: 8px;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
}

.ps__rail-x {
    height: 8px;
    background-color: transparent !important;
    border-radius: 4px;
}

.ps__thumb-x {
    height: 6px;
    background: linear-gradient(90deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 4px;
    transition: all 0.2s ease;
}

.ps__rail-x:hover > .ps__thumb-x,
.ps__rail-x:focus > .ps__thumb-x {
    height: 8px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

/* Enhanced pagination styling */
.pagination-wrapper {
    position: relative;
    margin-top: 1.5rem;
    width: 100%;
    display: flex;
    justify-content: center;
}

.pagination-card {
    padding: 1rem;

    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    list-style: none;
    padding-left: 0;
    margin: 0;
    border-radius: 0.5rem;
    gap: 0.25rem;
}

.pagination li {
    margin: 0;
}

.pagination li a {
    color: #6b7280;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 0.875rem;
    line-height: 1.25;
    border: 1px solid transparent;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    font-weight: 500;
    min-width: 40px;
    background: transparent;
}

.pagination li.active a {
    z-index: 1;
    color: #fff;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: transparent;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    transform: translateY(-1px);
}

.pagination li a:hover:not(.active) {
    color: #10b981;
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.1) 0%,
        rgba(16, 185, 129, 0.05) 100%
    );
    border-color: rgba(16, 185, 129, 0.2);
    transform: translateY(-1px);
}

/* Enhanced search container */
.search-container {
    max-width: 350px;
    width: 100%;
}

.search-input {
    height: 42px;
    transition: all 0.3s ease;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border: 1px solid #d1d5db;
    background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%);
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
    border-color: #10b981;
    background: #ffffff;
}

.input-group-text {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    background: linear-gradient(145deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid #d1d5db;
    border-right: none;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .table-scroll-container {
        max-height: calc(100vh - 300px);
        border-radius: 0.5rem;
    }

    .table thead th {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }

    .table td {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }

    .absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
        width: 90vw;
        max-width: 90vw;
        left: 50%;
        transform: translateX(-50%);
        margin-left: 0;
        margin-right: 0;
    }
}

/* Loading animation enhancement */
#loading-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    color: #10b981;
}
</style>
