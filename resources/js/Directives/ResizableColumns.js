export const ResizableColumns = {
    mounted(el, binding) {
        console.log("ResizableColumns mounted", el, binding.value);
        const options = binding.value || {};
        const minWidth = options.minWidth || 60;
        const saveState = options.saveState || false;
        const stateId = options.id || "default";
        const adjustTableWidth = options.adjustTableWidth !== false;

        // ถ้า element ไม่ใช่ตาราง ให้ล้มเลิกการทำงาน
        if (el.tagName !== "TABLE") {
            console.error(
                "Resizable columns directive can only be used on table elements"
            );
            return;
        }

        const table = el;
        const headerRow = table.querySelector("thead tr");

        if (!headerRow) {
            console.error("Table must have a thead and tr element");
            return;
        }

        const headers = headerRow.querySelectorAll("th");
        let tableWidth = table.offsetWidth;

        // เพิ่มคลาสให้กับตาราง
        table.classList.add("resizable");

        // เก็บความกว้างเริ่มต้นของตารางและคอลัมน์
        const initialTableWidth = tableWidth;
        const initialWidths = Array.from(headers).map(
            (header) => header.offsetWidth
        );

        // โหลดสถานะที่บันทึกไว้ (ถ้ามี)
        if (saveState) {
            loadSavedState(table, headers, stateId, adjustTableWidth);
        }

        // สร้างตัวปรับขนาดคอลัมน์
        headers.forEach((header, index) => {
            if (index < headers.length - 1) {
                // ไม่สร้างตัวปรับขนาดสำหรับคอลัมน์สุดท้าย
                createResizer(
                    header,
                    index,
                    headers,
                    table,
                    minWidth,
                    saveState,
                    stateId,
                    adjustTableWidth
                );
            }
        });
    },

    // เพิ่มการจัดการเมื่อ component ถูก unmounted
    unmounted(el) {
        // ไม่จำเป็นต้องทำอะไรเนื่องจากอีเวนต์ listener จะถูกลบออกอัตโนมัติ
        // เมื่อ element ถูกลบออกจาก DOM
        console.log("ResizableColumns unmounted");
    },
};

// ฟังก์ชันสร้างตัวปรับขนาดคอลัมน์
function createResizer(
    header,
    index,
    headers,
    table,
    minWidth,
    saveState,
    stateId,
    adjustTableWidth
) {
    // ตรวจสอบว่า header มี position relative หรือไม่
    const headerPosition = window
        .getComputedStyle(header)
        .getPropertyValue("position");
    if (headerPosition !== "relative") {
        header.style.position = "relative";
    }

    // สร้าง resizer element
    const resizer = document.createElement("div");
    resizer.className = "column-resizer";
    resizer.style.position = "absolute";
    resizer.style.top = "0";
    resizer.style.right = "0";
    resizer.style.width = "8px";
    resizer.style.height = "100%";
    resizer.style.cursor = "col-resize";
    resizer.style.zIndex = "100";
    resizer.style.backgroundColor = "transparent";

    header.appendChild(resizer);

    // ตัวแปรเก็บค่าเริ่มต้นสำหรับการปรับขนาด
    let startX, startWidth, nextStartWidth;
    const nextHeader = headers[index + 1];

    // ฟังก์ชันเริ่มต้นการปรับขนาด
    const startResize = function (e) {
        e.preventDefault();
        startX = e.clientX || (e.touches && e.touches[0].clientX);
        startWidth = header.getBoundingClientRect().width;
        nextStartWidth = nextHeader.getBoundingClientRect().width;

        // เพิ่มคลาสให้รู้ว่ากำลังปรับขนาด
        resizer.classList.add("resizing");
        table.classList.add("resizing");

        // เพิ่ม event listener สำหรับ mouse move และ mouse up
        document.addEventListener("mousemove", onResize);
        document.addEventListener("mouseup", stopResize);
        document.addEventListener("touchmove", onResize, { passive: false });
        document.addEventListener("touchend", stopResize);

        // ป้องกันการเลือกข้อความระหว่างปรับขนาด
        document.body.style.userSelect = "none";
    };

    // ฟังก์ชันปรับขนาดตามการเคลื่อนที่ของเมาส์
    const onResize = function (e) {
        e.preventDefault();
        const currentX = e.clientX || (e.touches && e.touches[0].clientX);
        const diffX = currentX - startX;

        if (adjustTableWidth) {
            // โหมดปรับขนาดทั้งตาราง
            const newWidth = Math.max(minWidth, startWidth + diffX);
            header.style.width = `${newWidth}px`;
            table.style.width = `${
                table.getBoundingClientRect().width + diffX
            }px`;
        } else {
            // โหมดปรับขนาดเฉพาะคอลัมน์ปัจจุบันและถัดไป
            const newWidth = Math.max(minWidth, startWidth + diffX);
            const newNextWidth = Math.max(minWidth, nextStartWidth - diffX);

            header.style.width = `${newWidth}px`;
            nextHeader.style.width = `${newNextWidth}px`;
        }
    };

    // ฟังก์ชันหยุดการปรับขนาด
    const stopResize = function () {
        // ลบ event listener
        document.removeEventListener("mousemove", onResize);
        document.removeEventListener("mouseup", stopResize);
        document.removeEventListener("touchmove", onResize);
        document.removeEventListener("touchend", stopResize);

        // ลบคลาส resizing
        resizer.classList.remove("resizing");
        table.classList.remove("resizing");

        // คืนค่า user-select
        document.body.style.userSelect = "";

        // บันทึกสถานะ
        if (saveState) {
            saveTableState(table, headers, stateId);
        }
    };

    // เพิ่ม event listener สำหรับการเริ่มปรับขนาด
    resizer.addEventListener("mousedown", startResize);
    resizer.addEventListener("touchstart", startResize, { passive: false });
}

// ฟังก์ชันบันทึกสถานะของตาราง
function saveTableState(table, headers, stateId) {
    const state = {
        tableWidth: table.offsetWidth,
        columns: Array.from(headers).map((header) => header.offsetWidth),
    };

    localStorage.setItem(`table-state-${stateId}`, JSON.stringify(state));
    console.log("Table state saved", state);
}

// ฟังก์ชันโหลดสถานะที่บันทึกไว้
function loadSavedState(table, headers, stateId, adjustTableWidth) {
    try {
        const savedState = localStorage.getItem(`table-state-${stateId}`);

        if (savedState) {
            const state = JSON.parse(savedState);

            // ปรับความกว้างของตาราง
            if (adjustTableWidth && state.tableWidth) {
                table.style.width = `${state.tableWidth}px`;
            }

            // ปรับความกว้างของคอลัมน์
            if (state.columns && state.columns.length === headers.length) {
                headers.forEach((header, index) => {
                    header.style.width = `${state.columns[index]}px`;
                });
            }
        }
    } catch (error) {
        console.error("Error loading table state:", error);
    }
}
