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
                    v-if="index !== breadcrumbs.length - 1 || item.customPath"
                    :to="item.customPath || item.path"
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
                Danhsachhoso: "Danh sách hồ sơ",
                taonewhoso: "Tạo mới hồ sơ",
                Details_NghiemthuDV: "Nghiệm thu dịch vụ",
                Details_Phieugiaonhanhomgiong: "Phiếu giao nhận hom giống",
                Details_Phieudenghithanhtoandichvu:
                    "Phiếu đề nghị thanh toán dịch vụ",
                Details_PhieudenghithanhtoanHomgiong:
                    "Phiếu đề nghị thanh toán hom giống",
                Details_Phieutrinhthanhtoan: "Phiếu trình thanh toán",
                Details_PhieutrinhthanhtoanHomgiong:
                    "Phiếu trình thanh toán hom giống",
                Details_Phieudenghithanhtoanhomgiong:
                    "Phiếu đề nghị thanh toán hom giống",
                Details_CongnoDichVuKhauTru: "Công nợ dịch vụ phải thu",
            },
            customPathMap: {
                Details_NghiemthuDV: "/Bienbannghiemthudichvu",
                Details_Phieugiaonhanhomgiong: "/Phieugiaonhanhomgiong",
                Details_Phieudenghithanhtoandichvu:
                    "/Phieudenghithanhtoandichvu",
                Details_PhieudenghithanhtoanHomgiong:
                    "/PhieudenghithanhtoanHomgiong",
                Details_Phieudenghithanhtoanhomgiong:
                    "/Phieudenghithanhtoanhomgiong",
                Details_Phieutrinhthanhtoan: "/Phieutrinhthanhtoan",
                Details_PhieutrinhthanhtoanHomgiong:
                    "/PhieutrinhthanhtoanHomgiong",
                Details_CongnoDichVuKhauTru: "/CongnoDichVuKhauTru",
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
                    customPath: this.customPathMap[segment],
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
