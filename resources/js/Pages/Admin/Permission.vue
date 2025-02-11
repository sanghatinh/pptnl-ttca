<template>
    <div>
        <div v-if="PermissionFromshow">
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h4>Permission Management</h4>
                </div>

                <div class="col text-end mb-2">
                    <button
                        type="button"
                        class="btn btn-success btn-rounded me-2"
                        @click="savePermissions"
                    >
                        <i class="bx bxs-save"></i>Save
                    </button>
                    <button
                        type="button"
                        @click="cancel"
                        class="btn btn-secondary btn-rounded"
                    >
                        <i class="bx bx-x"></i>Cancel
                    </button>
                </div>
            </div>
            <div class="row gutters">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Permission Checkbox</div>
                        </div>
                        <div class="card-body">
                            <div
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="custom-control custom-switch"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    :id="'permission-' + permission.id"
                                    v-model="selectedPermissions"
                                    :value="permission.id"
                                />
                                <label
                                    class="custom-control-label"
                                    :for="'permission-' + permission.id"
                                    >{{ permission.name }}</label
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
                            <div
                                v-for="component in components"
                                :key="component.id"
                                class="custom-control custom-switch"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    :id="'component-' + component.id"
                                    v-model="selectedComponents"
                                    :value="component.id"
                                />
                                <label
                                    class="custom-control-label"
                                    :for="'component-' + component.id"
                                    >{{ component.name }}</label
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else>
            <h4>Role Management</h4>
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
            permissions: [],
            components: [],
            selectedPermissions: [],
            selectedComponents: [],
            updatePermissionID: null,
            roles: [], // Add roles data
        };
    },
    methods: {
        updatePermission(roleID) {
            this.updatePermissionID = roleID;
            this.PermissionFromshow = true;
            this.fetchPermissions();
            this.fetchComponents();
            this.fetchRolePermissions(roleID);
            this.fetchRoleComponents(roleID);
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
        fetchPermissions() {
            axios
                .get("/api/permissions")
                .then((response) => {
                    this.permissions = response.data;
                })
                .catch((error) => {
                    console.error(
                        "There was an error fetching the permissions!",
                        error
                    );
                });
        },
        fetchComponents() {
            axios
                .get("/api/components")
                .then((response) => {
                    this.components = response.data;
                })
                .catch((error) => {
                    console.error(
                        "There was an error fetching the components!",
                        error
                    );
                });
        },
        fetchRolePermissions(roleID) {
            axios
                .get(`/api/role/${roleID}/permissions`)
                .then((response) => {
                    this.selectedPermissions = response.data.map(
                        (permission) => permission.permission_id
                    );
                })
                .catch((error) => {
                    console.error(
                        "There was an error fetching the role permissions!",
                        error
                    );
                });
        },
        fetchRoleComponents(roleID) {
            axios
                .get(`/api/role/${roleID}/components`)
                .then((response) => {
                    this.selectedComponents = response.data.map(
                        (component) => component.component_id
                    );
                })
                .catch((error) => {
                    console.error(
                        "There was an error fetching the role components!",
                        error
                    );
                });
        },
        savePermissions() {
            const data = {
                role_id: this.updatePermissionID,
                permissions: this.selectedPermissions,
                components: this.selectedComponents,
            };
            axios
                .post("/api/role/permissions", data)
                .then((response) => {
                    this.PermissionFromshow = false;
                    console.log("Permissions saved successfully!");
                })
                .catch((error) => {
                    console.error(
                        "There was an error saving the permissions!",
                        error
                    );
                });
        },
        cancel() {
            this.PermissionFromshow = false;
        },
    },
    mounted() {
        // Fetch roles data when the component is mounted
        axios
            .get("/api/roles")
            .then((response) => {
                this.roles = response.data;
            })
            .catch((error) => {
                console.error("There was an error fetching the roles!", error);
            });
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
