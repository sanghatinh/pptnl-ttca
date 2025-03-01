<template>
    <div>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <router-link to="/">Home</router-link>
            </li>
            <li
                v-for="(item, index) in breadcrumbs"
                :key="index"
                class="breadcrumb-item"
                :class="{ active: index === breadcrumbs.length - 1 }"
            >
                <router-link
                    v-if="index !== breadcrumbs.length - 1"
                    :to="item.path"
                >
                    {{ item.name }}
                </router-link>
                <span v-else>{{ item.name }}</span>
            </li>
        </ol>
    </div>
</template>

<script>
export default {
    data() {
        return {
            pathToNameMap: {
                danhsachhoso: "Danh sách hồ sơ",
                taonewhoso: "Tạo mới hồ sơ",
                editgiaonhanhoso: "Chỉnh sửa hồ sơ",
            },
        };
    },
    computed: {
        breadcrumbs() {
            const path = this.$route.path;
            const pathSegments = path.split("/").filter((segment) => segment);
            const breadcrumbs = [];

            let currentPath = "";
            pathSegments.forEach((segment) => {
                currentPath += `/${segment}`;
                breadcrumbs.push({
                    name: this.pathToNameMap[segment] || segment,
                    path: currentPath,
                });
            });

            return breadcrumbs;
        },
    },
};
</script>

<style scoped>
.breadcrumb {
    background-color: transparent;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}

.breadcrumb-item a {
    color: #01902d;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #666;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #666;
}
</style>
