<template>
    <div>
        <div v-if="PermissionFromshow">
            <h4>Permission Management</h4>
            <div class="row gutters">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Permission Checkbox</div>
                        </div>
                        <div class="card-body">
                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input check-all"
                                    id="checkAll"
                                    @change="toggleAll"
                                />
                                <label
                                    class="custom-control-label"
                                    for="checkAll"
                                    >Select All</label
                                >
                            </div>

                            <div class="mb-3"></div>

                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="customSwitch1"
                                />
                                <label
                                    class="custom-control-label"
                                    for="customSwitch1"
                                    >Toggle this switch element</label
                                >
                            </div>

                            <div class="mb-3"></div>

                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    checked=""
                                    id="customSwitch3"
                                />
                                <label
                                    class="custom-control-label"
                                    for="customSwitch3"
                                    >Enabled switch element</label
                                >
                            </div>

                            <div class="mb-3"></div>

                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    disabled=""
                                    id="customSwitch2"
                                />
                                <label
                                    class="custom-control-label"
                                    for="customSwitch2"
                                    >Disabled switch element</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row gutters">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Can view component</div>
                        </div>
                        <div class="card-body">
                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="customSwitch1"
                                />
                                <label
                                    class="custom-control-label"
                                    for="customSwitch1"
                                    >Toggle this switch element</label
                                >
                            </div>

                            <div class="mb-3"></div>

                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    checked=""
                                    id="customSwitch3"
                                />
                                <label
                                    class="custom-control-label"
                                    for="customSwitch3"
                                    >Enabled switch element</label
                                >
                            </div>

                            <div class="mb-3"></div>

                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    disabled=""
                                    id="customSwitch2"
                                />
                                <label
                                    class="custom-control-label"
                                    for="customSwitch2"
                                    >Disabled switch element</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else>
            <h4>Role Management</h4>
            <!-- Add your permission management UI here -->
            <div class="row gutters">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Role</th>
                                            <th>Action Permission</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Add your data here -->
                                        <tr
                                            v-for="(role, index) in roles"
                                            :key="role.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ role.id }}</td>
                                            <td :class="roleClasses(role.name)">
                                                <i
                                                    :class="{
                                                        'bx bx-user':
                                                            role.name ===
                                                            'User',
                                                        'bx bx-shield-quarter':
                                                            role.name ===
                                                            'Admin',
                                                        'bx bx-briefcase-alt':
                                                            role.name ===
                                                            'Manager',
                                                        'bx bx-crown':
                                                            role.name ===
                                                            'Super Admin',
                                                    }"
                                                ></i>
                                                {{ role.name }}
                                            </td>
                                            <td
                                                style="
                                                    text-align: center;
                                                    width: 180px;
                                                "
                                            >
                                                <a
                                                    class="btn btn-warning"
                                                    @click="
                                                        updatePermission(
                                                            role.id
                                                        )
                                                    "
                                                >
                                                    <i
                                                        class="bx bx-shield-quarter fs-6 text-bg-white"
                                                    ></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            PermissionFromshow: false,
            roles: [],
            updatePermissionID: null,
        };
    },
    mounted() {
        this.fetchRoles();
    },
    methods: {
        updatePermission(roleID) {
            this.updatePermissionID = roleID;
            this.PermissionFromshow = true;
            console.log("Update permission for role ID: ", roleID);
        },

        fetchRoles() {
            axios
                .get("/api/roles")
                .then((response) => {
                    this.roles = response.data;
                })
                .catch((error) => {
                    console.error(
                        "There was an error fetching the roles!",
                        error
                    );
                });
        },
        roleClasses(roleName) {
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
        },
    },
};
</script>

<style scoped>
/* Add your styles here */
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
</style>
