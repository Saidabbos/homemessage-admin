<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    services: Array,
    masters: Array,
});

// Wizard state: 1 | 2 | 3 | 'cart'
const step = ref(1);

// Single service selection (replaces per-person system)
const selectedService = ref(null); // { service_id, duration_id }

// Cart state
const cart = ref([]);

// Booking data
const booking = ref({
    master_id: null,
    date: null,
    slot: null,
    pressure_level: 'medium',
    notes: '',
});

// ==================== Service Selection ====================

const toggleService = (serviceId) => {
    if (selectedService.value?.service_id === serviceId) {
        selectedService.value = null;
    } else {
        const service = props.services?.find(s => s.id === serviceId);
        const defaultDuration = service?.durations?.find(d => d.is_default) || service?.durations?.[0];
        selectedService.value = {
            service_id: serviceId,
            duration_id: defaultDuration?.id || null,
        };
    }
};

const isServiceSelected = (serviceId) => selectedService.value?.service_id === serviceId;
const getServiceById = (serviceId) => props.services?.find(s => s.id === serviceId);

const getSelectedDuration = (serviceId) => {
    if (selectedService.value?.service_id !== serviceId) return null;
    const service = getServiceById(serviceId);
    return service?.durations?.find(d => d.id === selectedService.value.duration_id);
};

const setDuration = (serviceId, durationId) => {
    if (selectedService.value?.service_id === serviceId) {
        selectedService.value = { ...selectedService.value, duration_id: durationId };
    }
};

// ==================== Computed ====================

const currentItemDuration = computed(() => {
    if (!selectedService.value) return 0;
    const dur = getSelectedDuration(selectedService.value.service_id);
    return dur?.duration || 60;
});

const currentItemPrice = computed(() => {
    if (!selectedService.value) return 0;
    const dur = getSelectedDuration(selectedService.value.service_id);
    return Number(dur?.price) || 0;
});

const cartTotal = computed(() => cart.value.reduce((sum, item) => sum + item.price, 0));
const cartItemCount = computed(() => cart.value.length);

const selectedServiceIds = computed(() =>
    selectedService.value ? [selectedService.value.service_id] : []
);

// ==================== Master ====================

const selectedMaster = computed(() =>
    props.masters?.find(m => m.id === booking.value.master_id)
);

const filteredMasters = computed(() => {
    if (selectedServiceIds.value.length === 0) return props.masters || [];
    return (props.masters || []).filter(m =>
        selectedServiceIds.value.some(serviceId =>
            (m.service_type_ids || []).includes(serviceId)
        )
    );
});

// Reset master/date/slot when service changes
watch(() => selectedService.value?.service_id, () => {
    booking.value.master_id = null;
    booking.value.date = null;
    booking.value.slot = null;
});

// ==================== Dates & Slots ====================

const availableDates = computed(() => {
    const dates = [];
    const dayNames = ['Yak', 'Dush', 'Sesh', 'Chor', 'Pay', 'Jum', 'Shan'];
    const monthNames = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyn', 'Iyl', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];

    for (let i = 0; i < 7; i++) {
        const d = new Date();
        d.setDate(d.getDate() + i);
        dates.push({
            value: d.toISOString().split('T')[0],
            dayName: dayNames[d.getDay()],
            day: d.getDate(),
            month: monthNames[d.getMonth()],
            display: `${d.getDate()}-${monthNames[d.getMonth()]}`,
            fullLabel: `${d.getDate()}-${monthNames[d.getMonth()]}, ${dayNames[d.getDay()]}`,
        });
    }
    return dates;
});

const availableSlots = ref([]);
const loadingSlots = ref(false);

const loadSlots = async () => {
    loadingSlots.value = true;
    try {
        const duration = currentItemDuration.value || 60;
        const response = await fetch(`/api/masters/${booking.value.master_id}/slots?date=${booking.value.date}&duration=${duration}&people_count=1`);
        const data = await response.json();
        availableSlots.value = data.data?.slots || data.slots || [];
    } catch (e) {
        console.error('Failed to load slots:', e);
        availableSlots.value = [];
    }
    loadingSlots.value = false;
};

watch(
    [() => booking.value.master_id, () => booking.value.date, currentItemDuration],
    async ([masterId, date, duration]) => {
        if (masterId && date && duration > 0) {
            booking.value.slot = null;
            await loadSlots();
        } else {
            availableSlots.value = [];
        }
    }
);

// ==================== Formatting ====================

const formatPrice = (price) => {
    const num = Number(price) || 0;
    return new Intl.NumberFormat('uz-UZ').format(num);
};

const slotDisplay = computed(() => {
    if (!booking.value.slot) return '';
    const [hours, minutes] = booking.value.slot.split(':').map(Number);
    const endMinutes = minutes + 30;
    const endHours = hours + Math.floor(endMinutes / 60);
    const endMins = endMinutes % 60;
    return `${booking.value.slot}–${String(endHours).padStart(2, '0')}:${String(endMins).padStart(2, '0')}`;
});

const selectedDateLabel = computed(() => {
    const d = availableDates.value.find(d => d.value === booking.value.date);
    return d ? d.fullLabel : '';
});

const getTranslated = (field) => {
    if (typeof field === 'string') return field;
    if (field && typeof field === 'object') {
        return field.uz || field.ru || field.en || Object.values(field)[0] || '';
    }
    return '';
};

const selectedServiceSummary = computed(() => {
    if (!selectedService.value) return '';
    const service = getServiceById(selectedService.value.service_id);
    const duration = getSelectedDuration(selectedService.value.service_id);
    return `${getTranslated(service?.name)}, ${duration?.duration || 60} daq`;
});

// ==================== Navigation ====================

const canProceedStep1 = computed(() =>
    selectedService.value?.service_id && selectedService.value?.duration_id
);

const canProceedStep2 = computed(() =>
    booking.value.master_id && booking.value.date && booking.value.slot
);

const nextStep = () => { if (step.value < 3) step.value++; };

const prevStep = () => {
    if (step.value === 'cart') {
        step.value = 3;
    } else if (step.value > 1) {
        step.value--;
    }
};

// ==================== Cart ====================

const addToCart = () => {
    if (!canProceedStep2.value) return;

    const service = getServiceById(selectedService.value.service_id);
    const duration = getSelectedDuration(selectedService.value.service_id);
    const master = selectedMaster.value;
    const dateInfo = availableDates.value.find(d => d.value === booking.value.date);

    const cartItem = {
        id: Date.now() + Math.random(),
        service_id: selectedService.value.service_id,
        duration_id: selectedService.value.duration_id,
        master_id: booking.value.master_id,
        date: booking.value.date,
        slot: booking.value.slot,
        pressure_level: booking.value.pressure_level,
        notes: booking.value.notes,
        // Snapshot for display
        service_name: getTranslated(service?.name),
        duration_minutes: duration?.duration || 60,
        price: Number(duration?.price) || 0,
        master_name: master?.name || '',
        master_photo: master?.photo_url || null,
        date_label: dateInfo?.fullLabel || booking.value.date,
        slot_display: slotDisplay.value,
        pressure_label: pressureLevels.find(p => p.value === booking.value.pressure_level)?.label,
    };

    cart.value.push(cartItem);
    step.value = 'cart';
};

const removeFromCart = (itemId) => {
    cart.value = cart.value.filter(item => item.id !== itemId);
    if (cart.value.length === 0) {
        resetWizard();
    }
};

const bookAnotherMaster = () => {
    resetWizard();
};

const resetWizard = () => {
    selectedService.value = null;
    booking.value = {
        master_id: null,
        date: null,
        slot: null,
        pressure_level: 'medium',
        notes: '',
    };
    step.value = 1;
};

// ==================== Submit ====================

const submitting = ref(false);
const submitError = ref(null);

const submitCart = async () => {
    if (cart.value.length === 0) return;

    submitting.value = true;
    submitError.value = null;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const orders = cart.value.map(item => ({
            service_type_id: item.service_id,
            duration_id: item.duration_id,
            master_id: item.master_id,
            date: item.date,
            arrival_window_start: item.slot,
            total_duration: item.duration_minutes,
            pressure_level: item.pressure_level,
            notes: item.notes,
        }));

        const response = await fetch('/api/public/orders/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ orders }),
        });

        const data = await response.json();

        if (data.success) {
            const groupId = data.group_id || '';
            cart.value = [];
            router.visit(`/app/booking-success?group_id=${groupId}`);
        } else {
            submitError.value = data.message || 'Xatolik yuz berdi';
        }
    } catch (e) {
        console.error('Cart submission failed:', e);
        submitError.value = 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
    }
    submitting.value = false;
};

// ==================== Constants ====================

const pressureLevels = [
    { value: 'soft', label: 'Yengil' },
    { value: 'medium', label: "O'rtacha" },
    { value: 'hard', label: 'Kuchli' },
    { value: 'any', label: "Farqi yo'q" },
];
</script>

<template>
    <div class="bk-page">
        <!-- Background circles -->
        <div class="bg-circles">
            <div class="circle c1"></div>
            <div class="circle c2"></div>
            <div class="circle c3"></div>
        </div>

        <!-- Header -->
        <header class="bk-header glass">
            <button class="bk-back glass-btn" @click="step === 'cart' ? prevStep() : (step > 1 ? prevStep() : router.visit('/app'))">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <h1 class="bk-title">
                <span v-if="step === 1">Xizmat tanlang</span>
                <span v-else-if="step === 2">Usta va vaqt tanlang</span>
                <span v-else-if="step === 3">Tasdiqlash</span>
                <span v-else>Savat</span>
            </h1>
            <div class="bk-header-right">
                <button
                    v-if="step !== 'cart' && cartItemCount > 0"
                    class="bk-cart-btn glass-btn"
                    @click="step = 'cart'"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                    <span class="bk-cart-badge">{{ cartItemCount }}</span>
                </button>
                <span v-else class="bk-step-num">
                    <template v-if="step !== 'cart'">{{ step }}/3</template>
                </span>
            </div>
        </header>

        <!-- Step 1: Service Selection -->
        <div v-if="step === 1" class="bk-content">
            <!-- Service Cards -->
            <div class="service-list">
                <div
                    v-for="service in services"
                    :key="service.id"
                    class="service-card glass"
                    :class="{ selected: isServiceSelected(service.id) }"
                    @click="toggleService(service.id)"
                >
                    <div class="service-img">
                        <img v-if="service.image_url" :src="service.image_url" :alt="getTranslated(service.name)" />
                        <div v-else class="service-img-placeholder">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="service-info">
                        <h3 class="service-name">{{ getTranslated(service.name) }}</h3>
                        <p class="service-desc">{{ getTranslated(service.description) }}</p>
                        <span class="service-price">{{ formatPrice(service.durations?.[0]?.price || 0) }} - {{ formatPrice(service.durations?.[service.durations.length - 1]?.price || 0) }} UZS</span>
                    </div>
                    <div v-if="isServiceSelected(service.id)" class="service-check">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Duration Selection -->
            <div v-if="selectedService" class="section">
                <h3 class="section-title">
                    {{ getTranslated(getServiceById(selectedService.service_id)?.name) }} - Davomiyligi
                </h3>
                <div class="chip-row">
                    <button
                        v-for="dur in getServiceById(selectedService.service_id)?.durations"
                        :key="dur.id"
                        class="chip glass"
                        :class="{ selected: selectedService.duration_id === dur.id }"
                        @click="setDuration(selectedService.service_id, dur.id)"
                    >
                        {{ dur.duration }} min
                    </button>
                </div>
            </div>

            <!-- Pressure Level -->
            <div class="section">
                <h3 class="section-title">Kuch darajasi</h3>
                <div class="chip-row">
                    <button
                        v-for="level in pressureLevels"
                        :key="level.value"
                        class="chip glass"
                        :class="{ selected: booking.pressure_level === level.value }"
                        @click="booking.pressure_level = level.value"
                    >
                        {{ level.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Master & Time Selection -->
        <div v-if="step === 2" class="bk-content">
            <!-- Master Selection -->
            <div class="section">
                <h3 class="section-title">Ustani tanlang</h3>
                <div v-if="filteredMasters.length === 0" class="empty-hint">Bu xizmat uchun usta topilmadi</div>
                <div v-else class="master-list">
                    <div
                        v-for="master in filteredMasters"
                        :key="master.id"
                        class="master-card glass"
                        :class="{ selected: booking.master_id === master.id }"
                    >
                        <div class="master-photo">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <span v-else class="master-initial">{{ master.name?.charAt(0) }}</span>
                        </div>
                        <span class="master-name">{{ master.name }}</span>
                        <span class="master-exp">{{ master.experience || 3 }} yil</span>
                        <button
                            class="master-btn"
                            :class="{ active: booking.master_id === master.id }"
                            @click="booking.master_id = master.id; booking.slot = null;"
                        >
                            {{ booking.master_id === master.id ? 'Tanlangan' : 'Tanlash' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Date Selection -->
            <div class="section">
                <h3 class="section-title">Sana tanlang</h3>
                <div class="date-row">
                    <button
                        v-for="d in availableDates"
                        :key="d.value"
                        class="date-chip glass"
                        :class="{ selected: booking.date === d.value }"
                        @click="booking.date = d.value"
                    >
                        <span class="date-day">{{ d.dayName }}</span>
                        <span class="date-num">{{ d.display }}</span>
                    </button>
                </div>
            </div>

            <!-- Time Slots -->
            <div class="section">
                <h3 class="section-title">Vaqt tanlang</h3>
                <div v-if="!booking.master_id" class="empty-hint">Avval ustani tanlang</div>
                <div v-else-if="!booking.date" class="empty-hint">Avval sanani tanlang</div>
                <div v-else-if="loadingSlots" class="loading">
                    <div class="spinner"></div>
                </div>
                <div v-else-if="availableSlots.length === 0" class="empty-hint">Bu kunga vaqt yo'q</div>
                <div v-else class="slots-grid">
                    <button
                        v-for="slot in availableSlots"
                        :key="slot.start"
                        class="slot-chip glass"
                        :class="{
                            selected: booking.slot === slot.start,
                            disabled: slot.disabled
                        }"
                        :disabled="slot.disabled"
                        @click="!slot.disabled && (booking.slot = slot.start)"
                    >
                        {{ slot.display }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Confirmation -->
        <div v-if="step === 3" class="bk-content">
            <div class="confirm-box glass">
                <h3 class="confirm-title">Buyurtma tafsilotlari</h3>

                <div class="confirm-row">
                    <span class="confirm-label">Xizmat:</span>
                    <span class="confirm-value">{{ selectedServiceSummary }}</span>
                </div>

                <div class="confirm-row">
                    <span class="confirm-label">Usta:</span>
                    <span class="confirm-value">{{ selectedMaster?.name }}</span>
                </div>

                <div class="confirm-row">
                    <span class="confirm-label">Sana:</span>
                    <span class="confirm-value">{{ selectedDateLabel }}</span>
                </div>

                <div class="confirm-row">
                    <span class="confirm-label">Vaqt:</span>
                    <span class="confirm-value">{{ slotDisplay }}</span>
                </div>

                <div class="confirm-row">
                    <span class="confirm-label">Bosim:</span>
                    <span class="confirm-value">{{ pressureLevels.find(p => p.value === booking.pressure_level)?.label }}</span>
                </div>

                <div class="confirm-total">
                    <span class="confirm-label">Narxi:</span>
                    <span class="confirm-price">{{ formatPrice(currentItemPrice) }} UZS</span>
                </div>
            </div>

            <!-- Notes -->
            <div class="section">
                <h3 class="section-title">Qo'shimcha izoh</h3>
                <textarea
                    v-model="booking.notes"
                    class="notes-input glass"
                    placeholder="Masalan: 5-qavat, 25-xonadon"
                    rows="3"
                ></textarea>
            </div>

            <div v-if="submitError" class="error-msg">{{ submitError }}</div>
        </div>

        <!-- Cart View -->
        <div v-if="step === 'cart'" class="bk-content">
            <div v-if="cart.length === 0" class="cart-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
                <p>Savat bo'sh</p>
            </div>

            <div v-else class="cart-items">
                <div
                    v-for="item in cart"
                    :key="item.id"
                    class="cart-item glass"
                >
                    <div class="cart-item-top">
                        <div class="cart-item-service">
                            <h4>{{ item.service_name }}</h4>
                            <span class="cart-item-duration">{{ item.duration_minutes }} min</span>
                        </div>
                        <button class="cart-item-remove" @click="removeFromCart(item.id)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-row">
                            <span class="cart-item-label">Usta</span>
                            <span class="cart-item-val">{{ item.master_name }}</span>
                        </div>
                        <div class="cart-item-row">
                            <span class="cart-item-label">Sana</span>
                            <span class="cart-item-val">{{ item.date_label }}</span>
                        </div>
                        <div class="cart-item-row">
                            <span class="cart-item-label">Vaqt</span>
                            <span class="cart-item-val">{{ item.slot_display }}</span>
                        </div>
                        <div class="cart-item-row">
                            <span class="cart-item-label">Bosim</span>
                            <span class="cart-item-val">{{ item.pressure_label }}</span>
                        </div>
                    </div>
                    <div class="cart-item-price">
                        {{ formatPrice(item.price) }} UZS
                    </div>
                </div>

                <!-- Cart Total -->
                <div class="cart-total glass">
                    <span class="cart-total-label">Jami:</span>
                    <span class="cart-total-value">{{ formatPrice(cartTotal) }} UZS</span>
                </div>
            </div>

            <div v-if="submitError" class="error-msg">{{ submitError }}</div>
        </div>

        <!-- Bottom Bar -->
        <div class="bk-bottom glass">
            <!-- Summary for steps 1-3 -->
            <div v-if="step === 1 && selectedService" class="bottom-summary">
                <div class="summary-text">
                    <span class="summary-label">Tanlangan:</span>
                    <span class="summary-value">{{ selectedServiceSummary }}</span>
                </div>
                <div class="summary-price">
                    <span class="price-label">Narxi:</span>
                    <span class="price-value">{{ formatPrice(currentItemPrice) }} UZS</span>
                </div>
            </div>

            <div v-if="step === 2 && booking.slot" class="bottom-summary">
                <div class="summary-text">
                    <span class="summary-label">Tanlangan:</span>
                    <span class="summary-value">{{ selectedServiceSummary }}</span>
                    <span class="summary-detail">{{ slotDisplay }}, {{ selectedMaster?.name }}</span>
                </div>
                <div class="summary-price">
                    <span class="price-label">Narxi:</span>
                    <span class="price-value">{{ formatPrice(currentItemPrice) }} UZS</span>
                </div>
            </div>

            <div v-if="step === 'cart' && cart.length > 0" class="bottom-summary">
                <div class="summary-text">
                    <span class="summary-label">Savat:</span>
                    <span class="summary-value">{{ cartItemCount }} ta xizmat</span>
                </div>
                <div class="summary-price">
                    <span class="price-label">Jami:</span>
                    <span class="price-value">{{ formatPrice(cartTotal) }} UZS</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="step !== 'cart'" class="bottom-actions">
                <button
                    v-if="step === 1"
                    class="bk-btn"
                    :disabled="!canProceedStep1"
                    @click="nextStep"
                >
                    Davom etish
                </button>
                <button
                    v-else-if="step === 2"
                    class="bk-btn"
                    :disabled="!canProceedStep2"
                    @click="nextStep"
                >
                    Davom etish
                </button>
                <button
                    v-else-if="step === 3"
                    class="bk-btn bk-btn-cart"
                    @click="addToCart"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                    Savatga qo'shish
                </button>
            </div>

            <div v-else class="bottom-actions cart-actions">
                <button
                    class="bk-btn bk-btn-secondary"
                    @click="bookAnotherMaster"
                >
                    Boshqa usta tanlash
                </button>
                <button
                    class="bk-btn"
                    :disabled="cart.length === 0 || submitting"
                    @click="submitCart"
                >
                    {{ submitting ? 'Yuborilmoqda...' : "To'lov qilish" }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Base */
.bk-page {
    min-height: 100vh;
    padding-bottom: 180px;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    position: relative;
    overflow-x: hidden;
}

/* Background circles */
.bg-circles {
    position: fixed;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(184, 163, 105, 0.3), rgba(107, 139, 164, 0.2));
    filter: blur(60px);
    animation: float 8s ease-in-out infinite;
}

.c1 { width: 200px; height: 200px; top: -50px; right: -50px; }
.c2 { width: 150px; height: 150px; bottom: 200px; left: -40px; animation-delay: -2s; }
.c3 { width: 100px; height: 100px; top: 40%; right: 10%; animation-delay: -4s; }

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.6; }
    50% { transform: translateY(-30px) scale(1.1); opacity: 0.8; }
}

/* Glass effect */
.glass {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.glass-btn {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Header */
.bk-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    position: sticky;
    top: 0;
    z-index: 100;
    border-radius: 0 0 20px 20px;
}

.bk-back {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.bk-back:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.bk-title {
    flex: 1;
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.bk-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bk-step-num {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
}

.bk-cart-btn {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    color: #B8A369;
    cursor: pointer;
    transition: all 0.3s ease;
}

.bk-cart-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.bk-cart-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    min-width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #B8A369;
    color: #1a2a3a;
    font-size: 10px;
    font-weight: 700;
    border-radius: 8px;
    padding: 0 4px;
}

/* Content */
.bk-content {
    padding: 16px;
    position: relative;
    z-index: 1;
}

/* Section */
.section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 12px;
}

/* Service Cards */
.service-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.service-card {
    display: flex;
    gap: 12px;
    padding: 14px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.service-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(184, 163, 105, 0.1), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.service-card:hover::before,
.service-card.selected::before {
    opacity: 1;
}

.service-card.selected {
    border-color: rgba(184, 163, 105, 0.5);
    box-shadow: 0 0 30px rgba(184, 163, 105, 0.2);
    transform: scale(1.02);
}

.service-img {
    width: 72px;
    height: 72px;
    border-radius: 16px;
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.1);
}

.service-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.service-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.3);
}

.service-info {
    flex: 1;
    min-width: 0;
}

.service-name {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 4px;
}

.service-desc {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0 0 6px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.service-price {
    font-size: 14px;
    font-weight: 600;
    color: #B8A369;
}

.service-check {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border-radius: 50%;
    color: #1a2a3a;
    animation: popIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes popIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Chips */
.chip-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.chip {
    padding: 12px 20px;
    border-radius: 30px;
    font-size: 14px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chip:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.chip.selected {
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border-color: transparent;
    color: #1a2a3a;
    font-weight: 600;
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.4);
}

/* Date Selection */
.date-row {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}

.date-row::-webkit-scrollbar { display: none; }

.date-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 18px;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 70px;
}

.date-chip:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.15);
}

.date-chip.selected {
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border-color: transparent;
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.4);
}

.date-day {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
}

.date-chip.selected .date-day {
    color: rgba(26, 42, 58, 0.7);
}

.date-num {
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    margin-top: 4px;
}

.date-chip.selected .date-num {
    color: #1a2a3a;
}

/* Slots */
.slots-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.slot-chip {
    padding: 14px 8px;
    border-radius: 14px;
    font-size: 13px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
}

.slot-chip:hover {
    transform: scale(1.05);
    background: rgba(255, 255, 255, 0.15);
}

.slot-chip.selected {
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border-color: transparent;
    color: #1a2a3a;
    font-weight: 600;
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.4);
}

.slot-chip.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    text-decoration: line-through;
    background: rgba(255, 255, 255, 0.03);
}

.slot-chip.disabled:hover {
    transform: none;
    background: rgba(255, 255, 255, 0.03);
}

/* Master List */
.master-list {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}

.master-list::-webkit-scrollbar { display: none; }

.master-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 16px;
    border-radius: 20px;
    min-width: 110px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.master-card:hover {
    transform: translateY(-5px);
}

.master-card.selected {
    border-color: rgba(184, 163, 105, 0.5);
    box-shadow: 0 8px 30px rgba(184, 163, 105, 0.3);
}

.master-photo {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, #B8A369, #6B8BA4);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.master-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.master-initial {
    font-size: 24px;
    font-weight: 600;
    color: #fff;
}

.master-name {
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    text-align: center;
}

.master-exp {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 10px;
    border-radius: 12px;
}

.master-btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border: none;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: #1a2a3a;
    cursor: pointer;
    transition: all 0.3s ease;
}

.master-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(184, 163, 105, 0.4);
}

.master-btn.active {
    background: rgba(184, 163, 105, 0.3);
    color: #B8A369;
}

/* Confirmation */
.confirm-box {
    border-radius: 24px;
    padding: 24px;
    margin-bottom: 24px;
}

.confirm-title {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.confirm-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}

.confirm-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
}

.confirm-value {
    font-size: 14px;
    font-weight: 500;
    color: #fff;
}

.confirm-total {
    display: flex;
    justify-content: space-between;
    padding-top: 16px;
    margin-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.confirm-price {
    font-size: 20px;
    font-weight: 700;
    color: #B8A369;
}

/* Notes */
.notes-input {
    width: 100%;
    padding: 16px;
    border-radius: 16px;
    font-size: 14px;
    color: #fff;
    resize: none;
    outline: none;
    transition: all 0.3s ease;
}

.notes-input::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.notes-input:focus {
    border-color: rgba(184, 163, 105, 0.5);
    box-shadow: 0 0 20px rgba(184, 163, 105, 0.2);
}

/* Cart */
.cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 48px 24px;
    color: rgba(255, 255, 255, 0.4);
    text-align: center;
}

.cart-empty p {
    font-size: 16px;
    margin: 0;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.cart-item {
    border-radius: 20px;
    padding: 16px;
    position: relative;
}

.cart-item-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.cart-item-service h4 {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 4px;
}

.cart-item-duration {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
}

.cart-item-remove {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(220, 38, 38, 0.2);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: 10px;
    color: #FCA5A5;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cart-item-remove:hover {
    background: rgba(220, 38, 38, 0.4);
    transform: scale(1.1);
}

.cart-item-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

.cart-item-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}

.cart-item-label {
    color: rgba(255, 255, 255, 0.4);
}

.cart-item-val {
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}

.cart-item-price {
    font-size: 18px;
    font-weight: 700;
    color: #B8A369;
    text-align: right;
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.cart-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-radius: 16px;
    margin-top: 8px;
}

.cart-total-label {
    font-size: 16px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
}

.cart-total-value {
    font-size: 22px;
    font-weight: 700;
    color: #B8A369;
}

/* Empty/Loading states */
.empty-hint {
    padding: 24px;
    text-align: center;
    color: rgba(255, 255, 255, 0.4);
    font-size: 14px;
}

.loading {
    padding: 24px;
    display: flex;
    justify-content: center;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-top-color: #B8A369;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Error */
.error-msg {
    padding: 14px 18px;
    background: rgba(220, 38, 38, 0.2);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: 14px;
    color: #FCA5A5;
    font-size: 14px;
    margin-top: 16px;
}

/* Bottom Bar */
.bk-bottom {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    border-radius: 24px 24px 0 0;
    z-index: 100;
}

.bottom-summary {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.summary-label {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
}

.summary-value {
    font-size: 14px;
    font-weight: 500;
    color: #fff;
}

.summary-detail {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
}

.summary-price {
    text-align: right;
}

.price-label {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    display: block;
}

.price-value {
    font-size: 18px;
    font-weight: 700;
    color: #B8A369;
}

.bottom-actions {
    display: flex;
    gap: 10px;
}

.cart-actions {
    display: flex;
    gap: 10px;
}

.bk-btn {
    flex: 1;
    padding: 18px;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border: none;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 600;
    color: #1a2a3a;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.bk-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(184, 163, 105, 0.5);
}

.bk-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

.bk-btn-cart {
    background: linear-gradient(135deg, #B8A369, #D4C89A);
}

.bk-btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
    box-shadow: none;
    flex: 0.6;
}

.bk-btn-secondary:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.2);
    box-shadow: none;
}
</style>
