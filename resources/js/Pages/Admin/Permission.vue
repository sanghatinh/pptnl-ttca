<template>
    <div>
        <h3>Permission Management</h3>
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
                                    <!-- <tr
                                        v-for="(role, index) in roles"
                                        :key="role.id"
                                    >
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ role.id }}</td>
                                        <td>{{ role.name }}</td>
                                        <td
                                            style="
                                                text-align: center;
                                                width: 180px;
                                            "
                                        >
                                            <a class="btn btn-warning" href="#">
                                                <i
                                                    class="bx bx-shield-quarter fs-6 text-bg-white"
                                                ></i>
                                            </a>
                                        </td>
                                    </tr> -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- <script>
import axios from "axios";

export default {
    data() {
        return {
            roles: [],
        };
    },
    mounted() {
        this.fetchRoles();
    },
    methods: {
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
    },
};
</script> -->

<style scoped>
/* Add your styles here */
</style>
