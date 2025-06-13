<template>
    <div class="container-fluid p-4">
        <!-- Permission Form Section -->
        <div v-if="PermissionFromshow" class="fade-in">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1 text-primary">
                        <i class="bx bx-shield-quarter me-2"></i>Permission
                        Management
                    </h3>
                    <p class="text-muted mb-0">
                        Manage role permissions and component access
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button
                        type="button"
                        class="btn btn-success btn-modern"
                        @click="savePermissions"
                        :disabled="loading"
                    >
                        <i class="bx bxs-save me-1"></i>
                        {{ loading ? "Saving..." : "Save Changes" }}
                    </button>
                    <button
                        type="button"
                        @click="cancel"
                        class="btn btn-outline-danger"
                    >
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <!-- Permissions Card -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card card-modern h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-key me-2"></i>Permissions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div
                                class="form-check form-switch mb-3 p-3 bg-light rounded"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="select-all-permissions"
                                    v-model="allPermissionsSelected"
                                />
                                <label
                                    class="form-check-label fw-bold"
                                    for="select-all-permissions"
                                >
                                    Select All Permissions
                                </label>
                            </div>
                            <div class="permission-list">
                                <div
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="form-check form-switch mb-2 p-2 hover-bg"
                                >
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        :id="'permission-' + permission.id"
                                        v-model="selectedPermissions"
                                        :value="permission.id"
                                    />
                                    <label
                                        class="form-check-label"
                                        :for="'permission-' + permission.id"
                                    >
                                        {{ permission.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Components Card -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card card-modern h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-grid-alt me-2"></i>Components
                            </h5>
                        </div>
                        <div class="card-body">
                            <div
                                class="form-check form-switch mb-3 p-3 bg-light rounded"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="select-all-components"
                                    v-model="allComponentsSelected"
                                />
                                <label
                                    class="form-check-label fw-bold"
                                    for="select-all-components"
                                >
                                    Select All Components
                                </label>
                            </div>
                            <div class="component-list">
                                <div
                                    v-for="component in components"
                                    :key="component.id"
                                    class="form-check form-switch mb-2 p-2 hover-bg"
                                >
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        :id="'component-' + component.id"
                                        v-model="selectedComponents"
                                        :value="component.id"
                                    />
                                    <label
                                        class="form-check-label"
                                        :for="'component-' + component.id"
                                    >
                                        {{ component.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard -->
        <div v-else class="fade-in">
            <!-- Header Section -->
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4"
            >
                <div>
                    <h2 class="mb-1 text-primary">
                        <i class="bx bx-cog me-2"></i>Access Control Management
                    </h2>
                    <p class="text-muted mb-0">
                        Manage roles, permissions, and system access
                    </p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button
                        class="btn btn-primary btn-modern"
                        @click="showAddRoleModal"
                    >
                        <i class="bx bx-plus me-1"></i>Add Role
                    </button>
                    <button
                        class="btn btn-success btn-modern"
                        @click="showAddPermissionModal"
                    >
                        <i class="bx bx-shield-plus me-1"></i>Add Permission
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ roles.length }}</h4>
                                    <p class="mb-0">Total Roles</p>
                                </div>
                                <i
                                    class="bx bx-user-circle fs-1 opacity-50"
                                ></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">
                                        {{ permissions.length }}
                                    </h4>
                                    <p class="mb-0">Total Permissions</p>
                                </div>
                                <i class="bx bx-key fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">
                                        {{ components.length }}
                                    </h4>
                                    <p class="mb-0">Total Components</p>
                                </div>
                                <i class="bx bx-grid-alt fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">Active</h4>
                                    <p class="mb-0">System Status</p>
                                </div>
                                <i
                                    class="bx bx-check-circle fs-1 opacity-50"
                                ></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles Management -->
            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="card card-modern">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <i
                                    class="bx bx-user-circle me-2 text-primary"
                                ></i
                                >Role Management
                            </h5>
                            <button
                                class="btn btn-sm btn-primary"
                                @click="showAddRoleModal"
                            >
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive table-container">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light sticky-header">
                                        <tr>
                                            <th class="border-0">#</th>
                                            <th class="border-0">Role</th>
                                            <th class="border-0 text-center">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(role, index) in roles"
                                            :key="role.id"
                                            class="align-middle"
                                        >
                                            <td class="fw-bold">
                                                {{ index + 1 }}
                                            </td>
                                            <td>
                                                <div
                                                    class="d-flex align-items-center"
                                                >
                                                    <div
                                                        class="role-icon me-2"
                                                        :class="
                                                            roleIconClass(
                                                                role.name
                                                            )
                                                        "
                                                    >
                                                        <i
                                                            :class="
                                                                roleIcon(
                                                                    role.name
                                                                )
                                                            "
                                                        ></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">
                                                            {{ role.name }}
                                                        </div>
                                                        <small
                                                            class="text-muted"
                                                            >ID:
                                                            {{ role.id }}</small
                                                        >
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div
                                                    class="btn-group"
                                                    role="group"
                                                >
                                                    <button
                                                        class="btn btn-sm btn-outline-warning"
                                                        @click="
                                                            updatePermission(
                                                                role.id
                                                            )
                                                        "
                                                        title="Manage Permissions"
                                                    >
                                                        <i
                                                            class="bx bx-shield-quarter"
                                                        ></i>
                                                    </button>
                                                    <button
                                                        class="btn btn-sm btn-outline-primary"
                                                        @click="editRole(role)"
                                                        title="Edit Role"
                                                    >
                                                        <i
                                                            class="bx bx-edit"
                                                        ></i>
                                                    </button>
                                                    <button
                                                        class="btn btn-sm btn-outline-danger"
                                                        @click="
                                                            deleteRole(role.id)
                                                        "
                                                        title="Delete Role"
                                                        :disabled="
                                                            role.name ===
                                                            'Super Admin'
                                                        "
                                                    >
                                                        <i
                                                            class="bx bx-trash"
                                                        ></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Management -->
                <div class="col-xl-6">
                    <div class="card card-modern">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <i class="bx bx-key me-2 text-success"></i
                                >Permission Management
                            </h5>
                            <button
                                class="btn btn-sm btn-success"
                                @click="showAddPermissionModal"
                            >
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive table-container">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light sticky-header">
                                        <tr>
                                            <th class="border-0">#</th>
                                            <th class="border-0">Permission</th>
                                            <th class="border-0 text-center">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                permission, index
                                            ) in permissions"
                                            :key="permission.id"
                                            class="align-middle"
                                        >
                                            <td class="fw-bold">
                                                {{ index + 1 }}
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-medium">
                                                        {{ permission.name }}
                                                    </div>
                                                    <small class="text-muted"
                                                        >ID:
                                                        {{
                                                            permission.id
                                                        }}</small
                                                    >
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div
                                                    class="btn-group"
                                                    role="group"
                                                >
                                                    <button
                                                        class="btn btn-sm btn-outline-primary"
                                                        @click="
                                                            editPermission(
                                                                permission
                                                            )
                                                        "
                                                        title="Edit Permission"
                                                    >
                                                        <i
                                                            class="bx bx-edit"
                                                        ></i>
                                                    </button>
                                                    <button
                                                        class="btn btn-sm btn-outline-danger"
                                                        @click="
                                                            deletePermission(
                                                                permission.id
                                                            )
                                                        "
                                                        title="Delete Permission"
                                                    >
                                                        <i
                                                            class="bx bx-trash"
                                                        ></i>
                                                    </button>
                                                </div>
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

        <!-- Add/Edit Role Modal -->
        <div class="modal fade" id="roleModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bx bx-user-circle me-2"></i>
                            {{ editingRole ? "Edit Role" : "Add New Role" }}
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveRole">
                            <div class="mb-3">
                                <label for="roleName" class="form-label"
                                    >Role Name</label
                                >
                                <input
                                    type="text"
                                    class="form-control"
                                    id="roleName"
                                    v-model="roleForm.name"
                                    required
                                    placeholder="Enter role name"
                                />
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="saveRole"
                            :disabled="loading"
                        >
                            {{ loading ? "Saving..." : "Save Role" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Permission Modal -->
        <div class="modal fade" id="permissionModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bx bx-key me-2"></i>
                            {{
                                editingPermission
                                    ? "Edit Permission"
                                    : "Add New Permission"
                            }}
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="savePermission">
                            <div class="mb-3">
                                <label for="permissionName" class="form-label"
                                    >Permission Name</label
                                >
                                <input
                                    type="text"
                                    class="form-control"
                                    id="permissionName"
                                    v-model="permissionForm.name"
                                    required
                                    placeholder="Enter permission name"
                                />
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            @click="savePermission"
                            :disabled="loading"
                        >
                            {{ loading ? "Saving..." : "Save Permission" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { useStore } from "../../Store/Auth";
// Import Bootstrap bundle instead of selective import
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";

export default {
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    data() {
        return {
            PermissionFromshow: false,
            permissions: [],
            components: [],
            selectedPermissions: [],
            selectedComponents: [],
            updatePermissionID: null,
            roles: [],
            loading: false,
            editingRole: false,
            editingPermission: false,
            roleForm: {
                id: null,
                name: "",
            },
            permissionForm: {
                id: null,
                name: "",
            },
            // Store modal instances
            roleModalInstance: null,
            permissionModalInstance: null,
        };
    },
    computed: {
        allPermissionsSelected: {
            get() {
                return (
                    this.permissions.length > 0 &&
                    this.selectedPermissions.length === this.permissions.length
                );
            },
            set(value) {
                this.selectedPermissions = value
                    ? this.permissions.map((permission) => permission.id)
                    : [];
            },
        },
        allComponentsSelected: {
            get() {
                return (
                    this.components.length > 0 &&
                    this.selectedComponents.length === this.components.length
                );
            },
            set(value) {
                this.selectedComponents = value
                    ? this.components.map((component) => component.id)
                    : [];
            },
        },
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
        roleIcon(roleName) {
            switch (roleName) {
                case "User":
                    return "bx bx-user";
                case "Admin":
                    return "bx bx-shield-quarter";
                case "Manager":
                    return "bx bx-briefcase-alt";
                case "Super Admin":
                    return "bx bx-crown";
                default:
                    return "bx bx-user";
            }
        },
        roleIconClass(roleName) {
            switch (roleName) {
                case "User":
                    return "bg-primary";
                case "Admin":
                    return "bg-success";
                case "Manager":
                    return "bg-warning";
                case "Super Admin":
                    return "bg-danger";
                default:
                    return "bg-secondary";
            }
        },
        fetchPermissions() {
            axios
                .get("/api/permissions", {
                    headers: this.store.getAuthHeaders(),
                })
                .then((response) => {
                    this.permissions = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching permissions:", error);
                    this.showErrorToast("Error fetching permissions");
                });
        },
        fetchComponents() {
            axios
                .get("/api/components", {
                    headers: this.store.getAuthHeaders(),
                })
                .then((response) => {
                    this.components = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching components:", error);
                    this.showErrorToast("Error fetching components");
                });
        },
        fetchRoles() {
            axios
                .get("/api/roles", {
                    headers: this.store.getAuthHeaders(),
                })
                .then((response) => {
                    this.roles = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching roles:", error);
                    this.showErrorToast("Error fetching roles");
                });
        },
        fetchRolePermissions(roleID) {
            axios
                .get(`/api/role/${roleID}/permissions`, {
                    headers: this.store.getAuthHeaders(),
                })
                .then((response) => {
                    this.selectedPermissions = response.data.map(
                        (permission) => permission.permission_id
                    );
                })
                .catch((error) => {
                    console.error("Error fetching role permissions:", error);
                    this.showErrorToast("Error fetching role permissions");
                });
        },
        fetchRoleComponents(roleID) {
            axios
                .get(`/api/role/${roleID}/components`, {
                    headers: this.store.getAuthHeaders(),
                })
                .then((response) => {
                    this.selectedComponents = response.data.map(
                        (component) => component.component_id
                    );
                })
                .catch((error) => {
                    console.error("Error fetching role components:", error);
                    this.showErrorToast("Error fetching role components");
                });
        },
        savePermissions() {
            this.loading = true;
            const data = {
                role_id: this.updatePermissionID,
                permissions: this.selectedPermissions,
                components: this.selectedComponents,
            };
            axios
                .post("/api/role/permissions", data, {
                    headers: this.store.getAuthHeaders(),
                })
                .then((response) => {
                    this.PermissionFromshow = false;
                    this.showSuccessToast("Permissions saved successfully!");
                })
                .catch((error) => {
                    console.error("Error saving permissions:", error);
                    this.showErrorToast("Error saving permissions");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        cancel() {
            this.PermissionFromshow = false;
            this.selectedPermissions = [];
            this.selectedComponents = [];
        },
        // Role CRUD operations
        showAddRoleModal() {
            this.editingRole = false;
            this.roleForm = { id: null, name: "" };

            // Use global bootstrap object
            if (!this.roleModalInstance) {
                this.roleModalInstance = new bootstrap.Modal(
                    document.getElementById("roleModal"),
                    {
                        backdrop: true,
                        keyboard: true,
                    }
                );
            }
            this.roleModalInstance.show();
        },
        editRole(role) {
            this.editingRole = true;
            this.roleForm = { ...role };

            // Use global bootstrap object
            if (!this.roleModalInstance) {
                this.roleModalInstance = new bootstrap.Modal(
                    document.getElementById("roleModal"),
                    {
                        backdrop: true,
                        keyboard: true,
                    }
                );
            }
            this.roleModalInstance.show();
        },
        saveRole() {
            this.loading = true;
            const url = this.editingRole
                ? `/api/roles/${this.roleForm.id}`
                : "/api/roles";
            const method = this.editingRole ? "put" : "post";

            axios[method](
                url,
                { name: this.roleForm.name },
                {
                    headers: this.store.getAuthHeaders(),
                }
            )
                .then((response) => {
                    this.fetchRoles();

                    // Hide modal safely
                    if (this.roleModalInstance) {
                        this.roleModalInstance.hide();
                    }

                    const message = this.editingRole
                        ? "Role updated successfully!"
                        : "Role created successfully!";
                    this.showSuccessToast(message);
                })
                .catch((error) => {
                    console.error("Error saving role:", error);
                    this.showErrorToast("Error saving role");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        deleteRole(roleId) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios
                        .delete(`/api/roles/${roleId}`, {
                            headers: this.store.getAuthHeaders(),
                        })
                        .then((response) => {
                            this.fetchRoles();
                            this.showSuccessToast("Role deleted successfully!");
                        })
                        .catch((error) => {
                            console.error("Error deleting role:", error);
                            this.showErrorToast("Error deleting role");
                        });
                }
            });
        },
        // Permission CRUD operations
        showAddPermissionModal() {
            this.editingPermission = false;
            this.permissionForm = { id: null, name: "" };

            // Use global bootstrap object
            if (!this.permissionModalInstance) {
                this.permissionModalInstance = new bootstrap.Modal(
                    document.getElementById("permissionModal"),
                    {
                        backdrop: true,
                        keyboard: true,
                    }
                );
            }
            this.permissionModalInstance.show();
        },
        editPermission(permission) {
            this.editingPermission = true;
            this.permissionForm = { ...permission };

            // Use global bootstrap object
            if (!this.permissionModalInstance) {
                this.permissionModalInstance = new bootstrap.Modal(
                    document.getElementById("permissionModal"),
                    {
                        backdrop: true,
                        keyboard: true,
                    }
                );
            }
            this.permissionModalInstance.show();
        },
        savePermission() {
            this.loading = true;
            const url = this.editingPermission
                ? `/api/permissions/${this.permissionForm.id}`
                : "/api/permissions";
            const method = this.editingPermission ? "put" : "post";

            axios[method](
                url,
                { name: this.permissionForm.name },
                {
                    headers: this.store.getAuthHeaders(),
                }
            )
                .then((response) => {
                    this.fetchPermissions();

                    // Hide modal safely
                    if (this.permissionModalInstance) {
                        this.permissionModalInstance.hide();
                    }

                    const message = this.editingPermission
                        ? "Permission updated successfully!"
                        : "Permission created successfully!";
                    this.showSuccessToast(message);
                })
                .catch((error) => {
                    console.error("Error saving permission:", error);
                    this.showErrorToast("Error saving permission");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        deletePermission(permissionId) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios
                        .delete(`/api/permissions/${permissionId}`, {
                            headers: this.store.getAuthHeaders(),
                        })
                        .then((response) => {
                            this.fetchPermissions();
                            this.showSuccessToast(
                                "Permission deleted successfully!"
                            );
                        })
                        .catch((error) => {
                            console.error("Error deleting permission:", error);
                            this.showErrorToast("Error deleting permission");
                        });
                }
            });
        },
        // Toast notification methods
        showSuccessToast(message) {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener("mouseenter", Swal.stopTimer);
                    toast.addEventListener("mouseleave", Swal.resumeTimer);
                },
            });
        },
        showErrorToast(message) {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "error",
                title: message,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener("mouseenter", Swal.stopTimer);
                    toast.addEventListener("mouseleave", Swal.resumeTimer);
                },
            });
        },
        showInfoToast(message) {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "info",
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener("mouseenter", Swal.stopTimer);
                    toast.addEventListener("mouseleave", Swal.resumeTimer);
                },
            });
        },
        // Handle authentication error
        handleAuthError() {
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");
            this.store.logout();
            this.showErrorToast("Session expired. Please login again.");
            setTimeout(() => {
                this.$router.push("/login");
            }, 2000);
        },
        // Initialize modal instances
        initializeModals() {
            this.$nextTick(() => {
                const roleModalElement = document.getElementById("roleModal");
                const permissionModalElement =
                    document.getElementById("permissionModal");

                if (roleModalElement && !this.roleModalInstance) {
                    this.roleModalInstance = new bootstrap.Modal(
                        roleModalElement,
                        {
                            backdrop: true,
                            keyboard: true,
                        }
                    );
                }

                if (permissionModalElement && !this.permissionModalInstance) {
                    this.permissionModalInstance = new bootstrap.Modal(
                        permissionModalElement,
                        {
                            backdrop: true,
                            keyboard: true,
                        }
                    );
                }
            });
        },
    },
    mounted() {
        this.fetchRoles();
        this.fetchPermissions();
        this.fetchComponents();

        // Initialize modals after DOM is mounted
        this.initializeModals();

        // Add global error handler for axios
        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
                return Promise.reject(error);
            }
        );
    },
    beforeUnmount() {
        // Clean up modal instances
        if (this.roleModalInstance) {
            this.roleModalInstance.dispose();
        }
        if (this.permissionModalInstance) {
            this.permissionModalInstance.dispose();
        }
    },
};
</script>
<style scoped>
/* Modern Card Styles */
.card-modern {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.card-modern:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

/* Modern Button Styles */
.btn-modern {
    border-radius: 0.5rem;
    padding: 0.5rem 1.2rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.2);
}

/* Enhanced Action Buttons */
.btn-group .btn {
    border-radius: 0.5rem !important;
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    border: 2px solid transparent;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn-group .btn::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.2),
        transparent
    );
    transition: left 0.5s;
}

.btn-group .btn:hover::before {
    left: 100%;
}

/* Permission Button */
.btn-outline-warning {
    color: #f59e0b;
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.btn-outline-warning:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border-color: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
}

/* Edit Button */
.btn-outline-primary {
    color: #3b82f6;
    border-color: #3b82f6;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
}

.btn-outline-primary:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* Delete Button */
.btn-outline-danger {
    color: #ef4444;
    border-color: #ef4444;
    background: linear-gradient(135deg, #fee2e2, #fecaca);
}

.btn-outline-danger:hover:not(:disabled) {
    color: #ffffff;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border-color: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

.btn-outline-danger:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f3f4f6;
    color: #9ca3af;
    border-color: #d1d5db;
}

/* Success Button for Add Permission */
.btn-outline-success {
    color: #10b981;
    border-color: #10b981;
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
}

.btn-outline-success:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #10b981, #059669);
    border-color: #059669;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

/* Button Icons */
.btn i {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.btn:hover i {
    transform: scale(1.1);
}

/* Button Group Improvements */
.btn-group {
    gap: 0.25rem;
    border-radius: 0.75rem;
    padding: 0.25rem;
    background: rgba(248, 250, 252, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.5);
}

/* Action Cell Styling */
.table td.text-center {
    padding: 1rem 0.75rem;
}

/* Responsive Button Group */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: row;
        justify-content: center;
        gap: 0.125rem;
        background: transparent;
        border: none;
        padding: 0;
    }

    .btn-group .btn {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        margin: 0 0.05rem;
    }

    .btn-group .btn i {
        font-size: 0.875rem;
    }
}

/* Add hover effect for table rows */
.table-hover tbody tr {
    transition: all 0.3s ease;
}

.table-hover tbody tr:hover {
    background: linear-gradient(
        135deg,
        rgba(59, 130, 246, 0.05),
        rgba(16, 185, 129, 0.05)
    );
    transform: scale(1.005);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Enhanced tooltip-like effect */
.btn[title] {
    position: relative;
}

.btn[title]:hover:after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 0.25rem;
}

.btn[title]:hover:before {
    content: "";
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.8);
    z-index: 1000;
}

/* Stats Cards */
.card-stats {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card-stats:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
}

/* Role Icon */
.role-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

/* Hover Effects */
.hover-bg {
    border-radius: 0.5rem;
    transition: background-color 0.2s ease;
}

.hover-bg:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Table Container with Scrollbar */
.table-container {
    max-height: 400px;
    overflow-y: auto;
    border-radius: 0 0 0.75rem 0.75rem;
}

/* Sticky Header */
.sticky-header {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8f9fa !important;
}

/* Custom Scrollbar for Tables */
.table-container::-webkit-scrollbar {
    width: 8px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Custom Scrollbar for Permission/Component Lists */
.permission-list,
.component-list {
    max-height: 300px;
    overflow-y: auto;
    padding: 0 0.5rem;
}

.permission-list::-webkit-scrollbar,
.component-list::-webkit-scrollbar {
    width: 6px;
}

.permission-list::-webkit-scrollbar-track,
.component-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.permission-list::-webkit-scrollbar-thumb,
.component-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
    transition: background 0.3s ease;
}

.permission-list::-webkit-scrollbar-thumb:hover,
.component-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animations */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Table Improvements */
.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #6c757d;
    padding: 1rem 0.75rem;
}

.table td {
    vertical-align: middle;
    padding: 0.75rem;
}

/* Form Controls */
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Modal Improvements */
.modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.modal-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 1rem 1rem 0 0;
}

.modal-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 0 0 1rem 1rem;
}

/* Loading State */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Scrollbar for Firefox */
.table-container,
.permission-list,
.component-list {
    padding: 0 2.5rem;
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.form-check.form-switch.mb-3.p-3.bg-light.rounded {
    margin-left: 1rem;
}
</style>
