<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    services: Array,
    masters: Array,
});

// Wizard state
const step = ref(1); // 1: Service, 2: Master & Time, 3: Confirm

// Booking data
const booking = ref({
    service_id: null,
    duration_id: null,
    master_id: null,
    date: null,
    slot: null,
    people_count: 1,
    pressure_level: 'medium',
    notes: '',
});

// Selected objects (for display)
const selectedService = computed(() => 
    props.services?.find(s => s.id === booking.value.service_id)
);

const selectedDuration = computed(() => 
    selectedService.value?.durations?.find(d => d.id === booking.value.duration_id)
);

const selectedMaster = computed(() => 
    props.masters?.find(m => m.id === booking.value.master_id)
);

// Filtered masters based on selected service
const filteredMasters = computed(() => {
    if (!booking.value.service_id) return props.masters || [];
    return (props.masters || []).filter(m => 
        m.service_type_ids?.includes(booking.value.service_id)
    );
});

// Reset master when service changes
watch(() => booking.value.service_id, () => {
    booking.value.master_id = null;
    booking.value.date = null;
    booking.value.slot = null;
});

// All unique durations across all services
const allDurations = computed(() => {
    const durations = new Set();
    props.services?.forEach(s => {
        s.durations?.forEach(d => durations.add(d.duration));
    });
    return Array.from(durations).sort((a, b) => a - b);
});

// Selected duration in minutes (for filter display)
const selectedDurationMinutes = computed(() => selectedDuration.value?.duration);

// Filter by duration - select first service that has this duration
const filterByDuration = (durationMinutes) => {
    // Find a service with this duration
    for (const service of props.services || []) {
        const dur = service.durations?.find(d => d.duration === durationMinutes);
        if (dur) {
            booking.value.service_id = service.id;
            booking.value.duration_id = dur.id;
            return;
        }
    }
};

// Available dates (next 7 days)
const availableDates = computed(() => {
    const dates = [];
    const today = new Date();
    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        dates.push({
            value: date.toISOString().split('T')[0],
            label: formatDate(date),
            dayName: date.toLocaleDateString('uz-UZ', { weekday: 'short' }),
            day: date.getDate(),
        });
    }
    return dates;
});

// Slots for selected master and date
const availableSlots = ref([]);
const loadingSlots = ref(false);

watch(
    [() => booking.value.master_id, () => booking.value.date, () => booking.value.duration_id, () => booking.value.people_count], 
    async ([masterId, date, durationId]) => {
        if (masterId && date && durationId) {
            booking.value.slot = null; // Reset slot when params change
            await loadSlots();
        } else {
            availableSlots.value = [];
        }
    }
);

const loadSlots = async () => {
    loadingSlots.value = true;
    try {
        const duration = selectedDuration.value?.duration || 60;
        const response = await fetch(`/api/masters/${booking.value.master_id}/slots?date=${booking.value.date}&duration=${duration}&people_count=${booking.value.people_count}`);
        const data = await response.json();
        availableSlots.value = data.data?.slots || data.slots || [];
    } catch (e) {
        console.error('Failed to load slots:', e);
        availableSlots.value = [];
    }
    loadingSlots.value = false;
};

const formatDate = (date) => {
    return date.toLocaleDateString('uz-UZ', { 
        month: 'short', 
        day: 'numeric' 
    });
};

const formatPrice = (price) => {
    return (price / 1000).toFixed(0) + 'K';
};

// Step navigation
const canProceedStep1 = computed(() => 
    booking.value.service_id && booking.value.duration_id
);

const canProceedStep2 = computed(() => 
    booking.value.master_id && booking.value.date && booking.value.slot
);

const nextStep = () => {
    if (step.value < 3) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

// Submit booking
const submitting = ref(false);
const submitError = ref(null);

const submitBooking = async () => {
    submitting.value = true;
    submitError.value = null;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/api/miniapp/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                service_type_id: booking.value.service_id,
                duration_id: booking.value.duration_id,
                master_id: booking.value.master_id,
                date: booking.value.date,
                arrival_window_start: booking.value.slot,
                people_count: booking.value.people_count,
                pressure_level: booking.value.pressure_level,
                notes: booking.value.notes,
            }),
        });
        
        const result = await response.json();
        
        if (result.success) {
            router.visit('/app/booking-success');
        } else {
            submitError.value = result.message || 'Xatolik yuz berdi';
        }
    } catch (e) {
        console.error('Booking failed:', e);
        submitError.value = 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
    }
    submitting.value = false;
};

// Select service
const selectService = (serviceId) => {
    booking.value.service_id = serviceId;
    // Auto-select default duration
    const service = props.services?.find(s => s.id === serviceId);
    const defaultDuration = service?.durations?.find(d => d.is_default) || service?.durations?.[0];
    if (defaultDuration) {
        booking.value.duration_id = defaultDuration.id;
    }
};

// Pressure levels
const pressureLevels = [
    { value: 'light', label: 'Yengil', icon: '○' },
    { value: 'medium', label: "O'rtacha", icon: '◐' },
    { value: 'strong', label: 'Kuchli', icon: '●' },
];
</script>

<template>
    <div class="booking-page">
        <!-- Background -->
        <div class="bg-gradient"></div>
        
        <!-- Progress bar -->
        <div class="progress-bar">
            <div class="progress-fill" :style="{ width: (step / 3 * 100) + '%' }"></div>
        </div>

        <!-- Header -->
        <header class="booking-header">
            <button v-if="step > 1" class="back-btn" @click="prevStep">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <button v-else class="back-btn" @click="router.visit('/app')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <h1 class="header-title">
                <span v-if="step === 1">Xizmat tanlang</span>
                <span v-else-if="step === 2">Vaqt tanlang</span>
                <span v-else>Tasdiqlash</span>
            </h1>
            <div class="step-indicator">{{ step }}/3</div>
        </header>

        <!-- Step 1: Service Selection -->
        <div v-if="step === 1" class="step-content">
            <!-- Services slider -->
            <h3 class="section-label">Massaj turi</h3>
            <div class="services-slider">
                <div 
                    v-for="service in services" 
                    :key="service.id"
                    class="service-slide"
                    :class="{ selected: booking.service_id === service.id }"
                    @click="selectService(service.id)"
                >
                    <div class="slide-image" v-if="service.image_url">
                        <img :src="service.image_url" :alt="service.name" />
                    </div>
                    <div class="slide-image slide-placeholder" v-else>
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                        </svg>
                    </div>
                    <span class="slide-name">{{ service.name }}</span>
                    <div class="slide-check" v-if="booking.service_id === service.id">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Duration filter -->
            <div class="duration-section">
                <h3 class="section-label">Davomiylik</h3>
                <div class="duration-chips">
                    <button 
                        v-for="dur in allDurations" 
                        :key="dur"
                        class="duration-chip"
                        :class="{ selected: selectedDurationMinutes === dur }"
                        @click="filterByDuration(dur)"
                    >
                        {{ dur }} min
                    </button>
                </div>
            </div>

            <!-- Pressure level filter -->
            <div class="pressure-section">
                <h3 class="section-label">Bosim kuchi</h3>
                <div class="pressure-chips">
                    <button 
                        v-for="level in pressureLevels" 
                        :key="level.value"
                        class="pressure-chip"
                        :class="{ selected: booking.pressure_level === level.value }"
                        @click="booking.pressure_level = level.value"
                    >
                        <span class="pressure-icon">{{ level.icon }}</span>
                        {{ level.label }}
                    </button>
                </div>
            </div>

            <!-- People count -->
            <div class="people-section">
                <h3 class="section-label">Necha kishi?</h3>
                <div class="people-selector">
                    <button 
                        class="people-btn"
                        :disabled="booking.people_count <= 1"
                        @click="booking.people_count--"
                    >-</button>
                    <span class="people-count">{{ booking.people_count }}</span>
                    <button 
                        class="people-btn"
                        :disabled="booking.people_count >= 5"
                        @click="booking.people_count++"
                    >+</button>
                </div>
            </div>

            <!-- Selected service info -->
            <div v-if="selectedService && selectedDuration" class="selected-info">
                <div class="info-row">
                    <span>{{ selectedService.name }} ({{ selectedDuration.duration }} min) × {{ booking.people_count }}</span>
                    <span class="info-price">{{ formatPrice(selectedDuration.price * booking.people_count) }}</span>
                </div>
            </div>
        </div>

        <!-- Step 2: Master & Time Selection -->
        <div v-if="step === 2" class="step-content">
            <!-- Master selection -->
            <div class="ma-section">
                <h3 class="section-label">Master tanlang</h3>
                <div v-if="filteredMasters.length === 0" class="no-masters">
                    Bu xizmat uchun master topilmadi
                </div>
                <div v-else class="ma-scroll">
                    <div 
                        v-for="master in filteredMasters" 
                        :key="master.id"
                        class="ma-card"
                        :class="{ selected: booking.master_id === master.id }"
                        @click="booking.master_id = master.id"
                    >
                        <div class="ma-avatar">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <span v-else>{{ master.name?.charAt(0) }}</span>
                        </div>
                        <span class="ma-name">{{ master.name }}</span>
                    </div>
                </div>
            </div>

            <!-- Date selection -->
            <div class="date-section">
                <h3 class="section-label">Sana</h3>
                <div class="dates-scroll">
                    <button 
                        v-for="d in availableDates" 
                        :key="d.value"
                        class="date-chip"
                        :class="{ selected: booking.date === d.value }"
                        @click="booking.date = d.value"
                    >
                        <span class="day-name">{{ d.dayName }}</span>
                        <span class="day-num">{{ d.day }}</span>
                    </button>
                </div>
            </div>

            <!-- Time slots -->
            <div v-if="booking.master_id && booking.date" class="slots-section">
                <h3 class="section-label">Kelish oynasi</h3>
                <div v-if="loadingSlots" class="loading-slots">
                    <div class="spinner"></div>
                </div>
                <div v-else-if="availableSlots.length === 0" class="no-slots">
                    Bu kunga bo'sh vaqt yo'q
                </div>
                <div v-else class="slots-grid">
                    <button 
                        v-for="slot in availableSlots" 
                        :key="slot.start"
                        class="slot-chip"
                        :class="{ selected: booking.slot === slot.start }"
                        @click="booking.slot = slot.start"
                    >
                        {{ slot.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Confirmation -->
        <div v-if="step === 3" class="step-content">
            <div class="summary-card">
                <h3 class="summary-title">Buyurtma ma'lumotlari</h3>
                
                <div class="summary-row">
                    <span class="label">Xizmat:</span>
                    <span class="value">{{ selectedService?.name }}</span>
                </div>
                
                <div class="summary-row">
                    <span class="label">Davomiylik:</span>
                    <span class="value">{{ selectedDuration?.duration }} daqiqa</span>
                </div>
                
                <div class="summary-row">
                    <span class="label">Master:</span>
                    <span class="value">{{ selectedMaster?.name }}</span>
                </div>
                
                <div class="summary-row">
                    <span class="label">Sana:</span>
                    <span class="value">{{ booking.date }}</span>
                </div>
                
                <div class="summary-row">
                    <span class="label">Kelish oynasi:</span>
                    <span class="value">{{ booking.slot }}</span>
                </div>
                
                <div class="summary-row">
                    <span class="label">Kishilar:</span>
                    <span class="value">{{ booking.people_count }}</span>
                </div>
                
                <div class="summary-row">
                    <span class="label">Bosim:</span>
                    <span class="value">{{ pressureLevels.find(p => p.value === booking.pressure_level)?.label }}</span>
                </div>

                <div class="summary-divider"></div>
                
                <div class="summary-row total">
                    <span class="label">Jami:</span>
                    <span class="value">{{ formatPrice((selectedDuration?.price || 0) * booking.people_count) }} so'm</span>
                </div>
            </div>

            <!-- Notes -->
            <div class="notes-section">
                <h3 class="section-label">Qo'shimcha izoh</h3>
                <textarea 
                    v-model="booking.notes"
                    class="notes-input"
                    placeholder="Masalan: 5-qavat, 25-xonadon"
                    rows="3"
                ></textarea>
            </div>

            <!-- Error message -->
            <div v-if="submitError" class="error-message">
                {{ submitError }}
            </div>
        </div>

        <!-- Bottom action -->
        <div class="bottom-action">
            <button 
                v-if="step === 1"
                class="action-btn"
                :disabled="!canProceedStep1"
                @click="nextStep"
            >
                Davom etish
            </button>
            <button 
                v-else-if="step === 2"
                class="action-btn"
                :disabled="!canProceedStep2"
                @click="nextStep"
            >
                Davom etish
            </button>
            <button 
                v-else
                class="action-btn confirm"
                :disabled="submitting"
                @click="submitBooking"
            >
                <span v-if="submitting">Yuborilmoqda...</span>
                <span v-else>Buyurtma berish</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.booking-page {
    min-height: 100vh;
    padding: 0 16px 100px;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    position: relative;
}

/* Progress bar */
.progress-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.1);
    z-index: 100;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #FF6B4A, #FF8F6B);
    transition: width 0.3s ease;
}

/* Header */
.booking-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 0;
    position: sticky;
    top: 0;
    background: transparent;
    z-index: 10;
}

.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 12px;
    color: #fff;
    cursor: pointer;
}

.header-title {
    flex: 1;
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.step-indicator {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
}

/* Step content */
.step-content {
    padding-top: 8px;
}

/* Section label */
.section-label {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin: 20px 0 12px;
}

.section-label:first-child {
    margin-top: 0;
}

/* Services slider */
.services-slider {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin: 0 -16px;
    padding-left: 16px;
    padding-right: 16px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.services-slider::-webkit-scrollbar {
    display: none;
}

.service-slide {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    min-width: 100px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.08);
    border: 2px solid transparent;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.service-slide.selected {
    border-color: #FF6B4A;
    background: rgba(255, 107, 74, 0.15);
}

.slide-image {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.1);
}

.slide-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slide-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.4);
}

.slide-name {
    font-size: 12px;
    font-weight: 500;
    color: #fff;
    text-align: center;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.slide-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #FF6B4A;
    border-radius: 50%;
    color: #fff;
}

/* Selected service info */
.selected-info {
    margin-top: 16px;
    padding: 16px;
    background: rgba(255, 107, 74, 0.1);
    border: 1px solid rgba(255, 107, 74, 0.3);
    border-radius: 16px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    font-size: 15px;
}

.info-price {
    font-weight: 700;
    color: #FF6B4A;
}

.check-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #FF6B4A;
    border-radius: 50%;
    color: #fff;
    flex-shrink: 0;
}

/* Duration chips */
.duration-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.duration-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: rgba(255, 255, 255, 0.08);
    border: 2px solid transparent;
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.duration-chip.selected {
    border-color: #FF6B4A;
    background: rgba(255, 107, 74, 0.15);
}

.duration-chip .price {
    color: #FF6B4A;
    font-weight: 600;
}

/* People selector */
.people-selector {
    display: flex;
    align-items: center;
    gap: 20px;
    justify-content: center;
    padding: 16px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 16px;
}

.people-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
}

.people-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.people-count {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    min-width: 40px;
    text-align: center;
}

/* Pressure chips */
.pressure-chips {
    display: flex;
    gap: 8px;
}

.pressure-chip {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.08);
    border: 2px solid transparent;
    border-radius: 12px;
    color: #fff;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pressure-chip.selected {
    border-color: #FF6B4A;
    background: rgba(255, 107, 74, 0.15);
}

.pressure-icon {
    font-size: 20px;
}

/* Master cards */
.ma-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin: 0 -16px;
    padding-left: 16px;
    padding-right: 16px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.ma-scroll::-webkit-scrollbar {
    display: none;
}

.ma-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 12px;
    min-width: 90px;
    background: rgba(255, 255, 255, 0.08);
    border: 2px solid transparent;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.ma-card.selected {
    border-color: #FF6B4A;
    background: rgba(255, 107, 74, 0.15);
}

.ma-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(255, 107, 74, 0.4), rgba(107, 139, 164, 0.4));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;
    font-size: 20px;
    overflow: hidden;
}

.ma-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ma-name {
    font-size: 13px;
    font-weight: 500;
    color: #fff;
    text-align: center;
    max-width: 70px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Date chips */
.dates-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}

.date-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.08);
    border: 2px solid transparent;
    border-radius: 12px;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 54px;
}

.date-chip.selected {
    border-color: #FF6B4A;
    background: rgba(255, 107, 74, 0.15);
}

.day-name {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
}

.day-num {
    font-size: 18px;
    font-weight: 600;
}

/* Time slots */
.slots-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.slot-chip {
    padding: 12px;
    background: rgba(255, 255, 255, 0.08);
    border: 2px solid transparent;
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.slot-chip.selected {
    border-color: #FF6B4A;
    background: rgba(255, 107, 74, 0.15);
}

.loading-slots,
.no-slots,
.no-masters {
    padding: 24px;
    text-align: center;
    color: rgba(255, 255, 255, 0.6);
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid rgba(255, 255, 255, 0.2);
    border-top-color: #FF6B4A;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Summary card */
.summary-card {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 20px;
}

.summary-title {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 16px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.summary-row .label {
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
}

.summary-row .value {
    color: #fff;
    font-size: 14px;
    font-weight: 500;
}

.summary-row.total {
    margin-top: 8px;
}

.summary-row.total .label,
.summary-row.total .value {
    font-size: 18px;
    font-weight: 700;
}

.summary-row.total .value {
    color: #FF6B4A;
}

.summary-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 12px 0;
}

/* Notes */
.notes-input {
    width: 100%;
    padding: 16px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    color: #fff;
    font-size: 14px;
    resize: none;
    outline: none;
}

.notes-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.notes-input:focus {
    border-color: #FF6B4A;
}

/* Bottom action */
.bottom-action {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    background: linear-gradient(transparent, rgba(26, 42, 58, 0.95) 30%);
}

.action-btn {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #FF6B4A, #FF8F6B);
    border: none;
    border-radius: 16px;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.action-btn.confirm {
    background: linear-gradient(135deg, #4CAF50, #66BB6A);
}

/* Error message */
.error-message {
    margin-top: 16px;
    padding: 12px 16px;
    background: rgba(244, 67, 54, 0.15);
    border: 1px solid rgba(244, 67, 54, 0.3);
    border-radius: 12px;
    color: #f44336;
    font-size: 14px;
    text-align: center;
}
</style>
