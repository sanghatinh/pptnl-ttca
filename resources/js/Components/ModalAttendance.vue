<template>
    <div
        class="fixed inset-0 top-0 left-0 w-full h-full z-[999] flex items-center justify-center bg-black bg-opacity-60 p-4"
    >
        <div
            class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-y-auto relative animate-fade-in"
        >
            <!-- Header -->
            <div
                class="sticky top-0 bg-white rounded-t-2xl border-b border-gray-200 px-6 py-4 z-10"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                Check-In / Check-Out
                            </h2>
                            <p class="text-sm text-gray-500">
                                Chấm công vào/ra
                            </p>
                        </div>
                    </div>
                    <button
                        @click="$emit('close')"
                        class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors duration-200"
                        aria-label="Close Modal"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6">
                <form @submit.prevent="submitAttendance" class="space-y-8">
                    <!-- Date Section -->
                    <div
                        class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100"
                    >
                        <label
                            class="flex items-center space-x-2 font-semibold mb-3 text-gray-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-blue-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <span>Ngày</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.date"
                            class="w-full md:w-auto border border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                            required
                            :disabled="!!log"
                        />
                    </div>

                    <!-- Photo Sections -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Morning Check-in -->
                        <div
                            class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-6 border border-amber-200 hover:shadow-lg transition-shadow duration-300"
                        >
                            <div class="flex items-center space-x-3 mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">
                                        CheckIn-Buổi Sáng
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Chụp ảnh khi bắt đầu làm việc
                                    </p>
                                </div>
                            </div>

                            <!-- Camera Section -->
                            <div class="space-y-4">
                                <div class="relative">
                                    <div
                                        class="border-2 border-dashed border-amber-300 rounded-xl p-8 text-center bg-white hover:bg-amber-50 transition-colors duration-200 cursor-pointer"
                                        @click="triggerCamera('morning')"
                                    >
                                        <div
                                            v-if="!previewMorning"
                                            class="space-y-3"
                                        >
                                            <div
                                                class="w-16 h-16 mx-auto bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-8 w-8 text-white"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-gray-700 font-medium"
                                                >
                                                    Click để chụp ảnh
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500 mt-1"
                                                >
                                                    Chỉ chấp nhận ảnh từ camera
                                                </p>
                                            </div>
                                        </div>
                                        <div v-else class="space-y-3">
                                            <img
                                                :src="previewMorning"
                                                class="w-32 h-32 mx-auto object-cover rounded-xl border-4 border-white shadow-lg"
                                            />
                                            <p
                                                class="text-sm text-green-600 font-medium"
                                            >
                                                ✓ Hình ảnh đã được chụp
                                            </p>
                                            <button
                                                type="button"
                                                @click.stop="
                                                    clearPhoto('morning')
                                                "
                                                class="text-xs text-amber-600 hover:text-amber-800 underline"
                                            >
                                                Chụp lại
                                            </button>
                                        </div>
                                    </div>
                                    <input
                                        ref="morningInput"
                                        type="file"
                                        accept="image/*"
                                        capture="environment"
                                        @change="
                                            onFileChange('morning', $event)
                                        "
                                        class="hidden"
                                    />
                                </div>

                                <!-- Location Fields -->
                                <div class="space-y-3">
                                    <label
                                        class="flex items-center space-x-2 text-sm font-medium text-gray-700"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-amber-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>
                                        <span>Toạ độ</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input
                                            type="number"
                                            step="0.000001"
                                            v-model="form.lat_morning"
                                            placeholder="Latitude"
                                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none bg-gray-50"
                                            readonly
                                        />
                                        <input
                                            type="number"
                                            step="0.000001"
                                            v-model="form.lng_morning"
                                            placeholder="Longitude"
                                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none bg-gray-50"
                                            readonly
                                        />
                                    </div>
                                </div>

                                <!-- Note Field -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Ghi chú
                                    </label>
                                    <textarea
                                        v-model="form.note_morning"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none resize-none"
                                        rows="3"
                                        placeholder="Ghi chú lý do đi làm buổi sáng..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Evening Check-out -->
                        <div
                            class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-200 hover:shadow-lg transition-shadow duration-300"
                        >
                            <div class="flex items-center space-x-3 mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">
                                        CheckIn-Buổi Chiều
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Chụp ảnh khi kết thúc làm việc
                                    </p>
                                </div>
                            </div>

                            <!-- Camera Section -->
                            <div class="space-y-4">
                                <div class="relative">
                                    <div
                                        class="border-2 border-dashed border-indigo-300 rounded-xl p-8 text-center bg-white hover:bg-indigo-50 transition-colors duration-200 cursor-pointer"
                                        @click="triggerCamera('evening')"
                                    >
                                        <div
                                            v-if="!previewEvening"
                                            class="space-y-3"
                                        >
                                            <div
                                                class="w-16 h-16 mx-auto bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-8 w-8 text-white"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-gray-700 font-medium"
                                                >
                                                    Click để chụp ảnh
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500 mt-1"
                                                >
                                                    Chỉ chấp nhận ảnh từ camera
                                                </p>
                                            </div>
                                        </div>
                                        <div v-else class="space-y-3">
                                            <img
                                                :src="previewEvening"
                                                class="w-32 h-32 mx-auto object-cover rounded-xl border-4 border-white shadow-lg"
                                            />
                                            <p
                                                class="text-sm text-green-600 font-medium"
                                            >
                                                ✓ Hình ảnh đã được chụp
                                            </p>
                                            <button
                                                type="button"
                                                @click.stop="
                                                    clearPhoto('evening')
                                                "
                                                class="text-xs text-indigo-600 hover:text-indigo-800 underline"
                                            >
                                                Chụp lại
                                            </button>
                                        </div>
                                    </div>
                                    <input
                                        ref="eveningInput"
                                        type="file"
                                        accept="image/*"
                                        capture="environment"
                                        @change="
                                            onFileChange('evening', $event)
                                        "
                                        class="hidden"
                                    />
                                </div>

                                <!-- Location Fields -->
                                <div class="space-y-3">
                                    <label
                                        class="flex items-center space-x-2 text-sm font-medium text-gray-700"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-indigo-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>
                                        <span>Toạ độ</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input
                                            type="number"
                                            step="0.000001"
                                            v-model="form.lat_evening"
                                            placeholder="Latitude"
                                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none bg-gray-50"
                                            readonly
                                        />
                                        <input
                                            type="number"
                                            step="0.000001"
                                            v-model="form.lng_evening"
                                            placeholder="Longitude"
                                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none bg-gray-50"
                                            readonly
                                        />
                                    </div>
                                </div>

                                <!-- Note Field -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Ghi chú
                                    </label>
                                    <textarea
                                        v-model="form.note_evening"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none resize-none"
                                        rows="3"
                                        placeholder="Ghi chú lý do đi làm buổi chiều..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <button
                            type="submit"
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-3 rounded-xl shadow-lg font-bold text-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none flex items-center space-x-2"
                            :disabled="submitting || !hasValidData()"
                        >
                            <svg
                                v-if="submitting"
                                class="animate-spin h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            <span>
                                {{
                                    submitting
                                        ? "Đang lưu..."
                                        : log
                                        ? "Lưu thay đổi"
                                        : "Lưu dữ liệu"
                                }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useStore } from "../Store/Auth";

const props = defineProps({
    log: Object,
});
const emit = defineEmits(["close", "saved"]);

const store = useStore();

function getUserIdFromLocalStorage() {
    try {
        const user = JSON.parse(localStorage.getItem("web_user"));
        return user?.id || "";
    } catch {
        return "";
    }
}

const form = ref({
    date: "",
    lat_morning: "",
    lng_morning: "",
    note_morning: "",
    lat_evening: "",
    lng_evening: "",
    note_evening: "",
});
const previewMorning = ref(null);
const previewEvening = ref(null);
const fileMorning = ref(null);
const fileEvening = ref(null);
const submitting = ref(false);
const morningInput = ref(null);
const eveningInput = ref(null);

watch(
    () => props.log,
    (log) => {
        if (log) {
            form.value = {
                date: log.date,
                lat_morning: log.lat_morning || "",
                lng_morning: log.lng_morning || "",
                note_morning: log.note_morning || "",
                lat_evening: log.lat_evening || "",
                lng_evening: log.lng_evening || "",
                note_evening: log.note_evening || "",
            };
            previewMorning.value = log.photo_morning
                ? getCloudinaryUrl(log.photo_morning)
                : null;
            previewEvening.value = log.photo_evening
                ? getCloudinaryUrl(log.photo_evening)
                : null;
        } else {
            form.value = {
                date: new Date().toISOString().slice(0, 10),
                lat_morning: "",
                lng_morning: "",
                note_morning: "",
                lat_evening: "",
                lng_evening: "",
                note_evening: "",
            };
            previewMorning.value = null;
            previewEvening.value = null;
        }
        fileMorning.value = null;
        fileEvening.value = null;
    },
    { immediate: true }
);

function getCloudinaryUrl(publicId) {
    const id = publicId.startsWith("attendance_logs/")
        ? publicId
        : `attendance_logs/${publicId}`;
    return `https://res.cloudinary.com/dhtgcccax/image/upload/${id}.jpg`;
}

function getLocationAndSet(type) {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            if (type === "morning") {
                form.value.lat_morning = pos.coords.latitude.toFixed(6);
                form.value.lng_morning = pos.coords.longitude.toFixed(6);
            } else {
                form.value.lat_evening = pos.coords.latitude.toFixed(6);
                form.value.lng_evening = pos.coords.longitude.toFixed(6);
            }
        },
        () => {
            if (type === "morning") {
                form.value.lat_morning = "";
                form.value.lng_morning = "";
            } else {
                form.value.lat_evening = "";
                form.value.lng_evening = "";
            }
        }
    );
}

function triggerCamera(type) {
    // Check if we're on mobile (has camera access)
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        if (type === "morning") {
            morningInput.value?.click();
        } else {
            eveningInput.value?.click();
        }
    } else {
        alert("กล้องถ่ายรูปไม่พร้อมใช้งาน กรุณาใช้งานผ่านมือถือ");
    }
}

function clearPhoto(type) {
    if (type === "morning") {
        previewMorning.value = null;
        fileMorning.value = null;
        form.value.lat_morning = "";
        form.value.lng_morning = "";
        if (morningInput.value) {
            morningInput.value.value = "";
        }
    } else {
        previewEvening.value = null;
        fileEvening.value = null;
        form.value.lat_evening = "";
        form.value.lng_evening = "";
        if (eveningInput.value) {
            eveningInput.value.value = "";
        }
    }
}

async function compressImage(file, maxSizeKB = 500, quality = 0.7) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const reader = new FileReader();
        reader.onload = (e) => {
            img.onload = () => {
                const canvas = document.createElement("canvas");
                // Resize to max 1024px
                const maxDim = 1024;
                let w = img.width,
                    h = img.height;
                if (w > maxDim || h > maxDim) {
                    if (w > h) {
                        h = Math.round(h * (maxDim / w));
                        w = maxDim;
                    } else {
                        w = Math.round(w * (maxDim / h));
                        h = maxDim;
                    }
                }
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, w, h);

                let currentQuality = quality;
                function tryCompress() {
                    canvas.toBlob(
                        (blob) => {
                            if (
                                blob.size / 1024 <= maxSizeKB ||
                                currentQuality < 0.4
                            ) {
                                resolve(blob);
                            } else {
                                currentQuality -= 0.1;
                                tryCompress();
                            }
                        },
                        "image/jpeg",
                        currentQuality
                    );
                }
                tryCompress();
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

async function onFileChange(type, event) {
    const file = event.target.files[0];
    if (!file) return;

    getLocationAndSet(type);

    // Compress image before preview/upload
    const compressedBlob = await compressImage(file, 500, 0.7);
    const compressedFile = new File([compressedBlob], file.name, {
        type: "image/jpeg",
    });

    const reader = new FileReader();
    reader.onload = (e) => {
        if (type === "morning") {
            previewMorning.value = e.target.result;
            fileMorning.value = compressedFile;
        } else {
            previewEvening.value = e.target.result;
            fileEvening.value = compressedFile;
        }
    };
    reader.readAsDataURL(compressedFile);
}

function hasValidData() {
    // At least one photo should be taken (either morning or evening)
    return (
        fileMorning.value ||
        previewMorning.value ||
        fileEvening.value ||
        previewEvening.value ||
        props.log
    ); // Allow if editing existing log
}

async function submitAttendance() {
    submitting.value = true;
    try {
        const formData = new FormData();

        // Add all form fields
        Object.entries(form.value).forEach(([key, val]) => {
            if (val) formData.append(key, val);
        });

        // Add user ID
        const userId = getUserIdFromLocalStorage();
        if (userId) {
            formData.append("users_id", userId);
        }

        // Add photos if selected
        if (fileMorning.value) {
            formData.append("photo_morning", fileMorning.value);
        }
        if (fileEvening.value) {
            formData.append("photo_evening", fileEvening.value);
        }

        let url = "/api/attendance-logs";
        let method = "POST";
        if (props.log) {
            url = `/api/attendance-logs/${props.log.id}`;
            method = "PUT";
        }

        // Get proper authentication headers
        const token = localStorage.getItem("web_token") || store.getToken;

        const response = await fetch(url, {
            method,
            body: formData,
            headers: {
                Authorization: `Bearer ${token}`,
                // Don't set Content-Type for FormData, let browser set it
            },
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "Request failed");
        }

        const result = await response.json();
        console.log("Attendance saved:", result);

        emit("saved");
        emit("close");
    } catch (error) {
        console.error("Error saving attendance:", error);
        alert("เกิดข้อผิดพลาด: " + error.message);
    } finally {
        submitting.value = false;
    }
}
</script>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: scale(0.96) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .grid-cols-1.lg\\:grid-cols-2 {
        grid-template-columns: 1fr;
    }

    .max-w-4xl {
        max-width: 100%;
        margin: 0;
        border-radius: 0;
    }

    .rounded-2xl {
        border-radius: 1rem;
    }
}
</style>
