<template lang="">
    <div>
        <div class="container my-5">
            <div class="progress-tracker">
                <!-- ขั้นตอนที่ 1 -->
                <div class="step">
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <p>ชำระเงินแล้ว</p>
                    <small>14 มิ.ย. 66</small>
                    <div class="line"></div>
                </div>

                <!-- ขั้นตอนที่ 2 -->
                <div class="step">
                    <div class="icon"><i class="fas fa-box"></i></div>
                    <p>จัดเตรียมสินค้า</p>
                    <small>14 มิ.ย. 66</small>
                    <div class="line"></div>
                </div>

                <!-- ขั้นตอนที่ 3 -->
                <div class="step">
                    <div class="icon"><i class="fas fa-truck"></i></div>
                    <p>อยู่ระหว่างการจัดส่ง</p>
                    <small>15 มิ.ย. 66</small>
                    <div class="line"></div>
                </div>

                <!-- ขั้นตอนที่ 4 -->
                <div class="step">
                    <div class="icon"><i class="fas fa-store"></i></div>
                    <p>สินค้าถึงร้านแล้ว</p>
                    <small>15 มิ.ย. 66</small>
                    <div class="line"></div>
                </div>

                <!-- ขั้นตอนที่ 5 -->
                <div class="step">
                    <div class="icon"><i class="fas fa-box-open"></i></div>
                    <p>รับสินค้าเรียบร้อยแล้ว</p>
                    <small>16 มิ.ย. 66</small>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {};
</script>
<style scoped>
/* ปรับแต่งเส้นเชื่อม */
.progress-tracker {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}
.step {
    text-align: center;
    position: relative;
    width: 150px;
}
.step .icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #28a745;
    color: #28a745;
    font-size: 24px;
    margin: auto;
}
.step .line {
    position: absolute;
    top: 30px;
    left: 75px;
    width: 100px;
    height: 3px;
    background-color: #28a745;
}
.step:last-child .line {
    display: none;
}
.step p {
    margin-top: 8px;
    font-size: 16px;
    font-weight: bold;
    color: #28a745;
}
.step small {
    display: block;
    font-size: 14px;
    color: #666;
}

/* ทำให้รองรับหน้าจอเล็ก */
@media (max-width: 767px) {
    .progress-tracker {
        flex-direction: column;
    }
    .step {
        width: 100%;
    }
    .step .line {
        display: none;
    }
}
</style>
