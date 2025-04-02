<template lang="">
    <!-- สร้างหน้า Unauthorized Access ให้สวยงามด้วย boostrap 5 + Css -->
    <div
        class="container min-vh-100 d-flex align-items-center justify-content-center"
    >
        <div class="text-center">
            <div class="error-icon mb-4">
                <i class="fas fa-exclamation-circle text-danger"></i>
            </div>
            <h1 class="display-1 fw-bold text-danger">404</h1>
            <h2 class="fs-1">Quyền truy cập bị từ chối</h2>
            <p class="lead">Bạn không có quyền truy cập vào tài liệu này.</p>
            <div class="mt-4">
                <router-link to="/Danhsachhoso" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách tài
                    liệu
                </router-link>
            </div>
        </div>
    </div>
</template>
<script>
export default {};
</script>
<style scoped>
.error-icon {
    font-size: 5rem;
    color: #dc3545;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>
