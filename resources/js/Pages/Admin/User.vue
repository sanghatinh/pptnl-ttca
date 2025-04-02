<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div>
        <div class="row align-items-center mb-4">
            <div class="col-12 col-md-6">
                <h3 class="mb-0 fw-bold text-primary">User Management</h3>
            </div>

            <!-- Show "Tạo mới" button only when user has 'create' permission -->
            <div class="col-12 col-md-6" v-if="!ShowFormUser">
                <div
                    class="d-flex flex-column flex-md-row justify-content-md-end gap-2"
                >
                    <div
                        class="search-container mb-2 mb-md-0 mt-2"
                        v-if="userHasPermission('create')"
                    >
                        <div class="input-group">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search users..."
                                class="form-control border-start-0 search-input"
                                aria-label="Search"
                            />
                        </div>
                    </div>
                    <button
                        v-if="userHasPermission('create')"
                        type="button"
                        @click="Adduser"
                        class="btn btn-success d-flex align-items-center mt-2"
                        style="height: 38px; font-size: 0.875rem"
                    >
                        <i class="bx bxs-user-plus me-1"></i>
                        <span>Tạo mới</span>
                    </button>
                </div>
            </div>

            <div class="col-12 col-md-6 mt-2" v-if="ShowFormUser">
                <div class="d-flex justify-content-md-end">
                    <button
                        type="button"
                        @click="Saveuser"
                        :disabled="FormValid"
                        class="btn btn-success me-2 d-flex align-items-center"
                    >
                        <i class="bx bxs-save me-1"></i>
                        <span>Save</span>
                    </button>
                    <button
                        type="button"
                        @click="Canneluser"
                        class="btn btn-secondary d-flex align-items-center"
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

                <div v-else>
                    <!-- แสดงตาลาง User ที่เราสร้างขึ้นมา -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Username</th>

                                            <th>Họ và tên</th>
                                            <th>Chức vụ</th>
                                            <th>Trạm nông vụ</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                user, index
                                            ) in paginatedUsers.data"
                                            :key="user.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ user.id }}</td>
                                            <td>{{ user.username }}</td>
                                            <td>{{ user.full_name }}</td>
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
                                                    <!-- แสดงเมนู dropdown ถ้าผู้ใช้มีสิทธิ์แก้ไขหรือลบ -->
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
    </div>
    {{ FormUser }}
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
            },
            FormType: true,
            EditID: "",
            isLoading: false,
        };
    },
    //สร้างการตรวจสอบฟอร์มใส่ลูกเล่นเมื่อผู้ใช้กรอกข้อมูลไม่ครบหรือเป็นถ้าว่างให้ขึ้นข้อความแสดงใน input นั้น
    computed: {
        // Add filtered users computed property
        filteredUsers() {
            return this.users.filter((user) => {
                const searchTerm = this.search.toLowerCase();
                return (
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
                        .includes(searchTerm)
                );
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
            //   errors.phone = 'Phone is required';
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
    },
    created() {
        this.fetchUsers();
        this.fetchPositions();
        this.fetchStations();
        this.fetchRoles();
        this.fetchUserPermissions(); // เรียกอ่าน permission เมื่อสร้าง component
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
    .search-input {
        min-width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>
