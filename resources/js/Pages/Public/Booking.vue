<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    services: Array,
    masters: Array,
    customer: Object,
});

// Wizard state
const step = ref(1); // 1: Service, 2: Master & Time, 3: Confirm

// Booking data - multi-select services
const booking = ref({
    services: [], // Array of { service_id, duration_id }
    people_count: 1,
    master_id: null,
    date: null,
    slot: null,
    pressure_level: 'medium',
    notes: '',
});

// Toggle service selection
const toggleService = (serviceId) => {
    const index = booking.value.services.findIndex(s => s.service_id === serviceId);
    if (index >= 0) {
        booking.value.services.splice(index, 1);
    } else {
        const service = props.services?.find(s => s.id === serviceId);
        const defaultDuration = service?.durations?.find(d => d.is_default) || service?.durations?.[0];
        booking.value.services.push({
            service_id: serviceId,
            duration_id: defaultDuration?.id || null,
        });
    }
};

const isServiceSelected = (serviceId) => {
    return booking.value.services.some(s => s.service_id === serviceId);
};

const getServiceById = (serviceId) => {
    return props.services?.find(s => s.id === serviceId);
};

const getSelectedDuration = (serviceId) => {
    const sel = booking.value.services.find(s => s.service_id === serviceId);
    const service = getServiceById(serviceId);
    return service?.durations?.find(d => d.id === sel?.duration_id);
};

const setDuration = (serviceId, durationId) => {
    const sel = booking.value.services.find(s => s.service_id === serviceId);
    if (sel) {
        sel.duration_id = durationId;
    }
};

// People count controls
const addPerson = () => {
    if (booking.value.people_count < 5) {
        booking.value.people_count++;
    }
};

const removePerson = () => {
    if (booking.value.people_count > 1) {
        booking.value.people_count--;
    }
};

// Total price calculation
const totalPrice = computed(() => {
    return booking.value.services.reduce((sum, sel) => {
        const duration = getSelectedDuration(sel.service_id);
        return sum + (duration?.price || 0);
    }, 0) * booking.value.people_count;
});

// Total duration calculation
const totalDuration = computed(() => {
    const baseTotal = booking.value.services.reduce((sum, sel) => {
        const duration = getSelectedDuration(sel.service_id);
        return sum + (duration?.duration || 0);
    }, 0);
    const buffer = 10;
    return baseTotal * booking.value.people_count + buffer * (booking.value.people_count - 1);
});

const selectedServiceIds = computed(() => {
    return booking.value.services.map(s => s.service_id);
});

const selectedMaster = computed(() => 
    props.masters?.find(m => m.id === booking.value.master_id)
);

const filteredMasters = computed(() => {
    if (selectedServiceIds.value.length === 0) return props.masters || [];
    return (props.masters || []).filter(m => 
        selectedServiceIds.value.every(serviceId => 
            m.service_type_ids?.includes(serviceId)
        )
    );
});

watch(selectedServiceIds, () => {
    booking.value.master_id = null;
    booking.value.date = null;
    booking.value.slot = null;
}, { deep: true });

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

// Slots
const availableSlots = ref([]);
const loadingSlots = ref(false);

watch(
    [() => booking.value.master_id, () => booking.value.date, totalDuration], 
    async ([masterId, date, duration]) => {
        if (masterId && date && duration > 0) {
            booking.value.slot = null;
            await loadSlots();
        } else {
            availableSlots.value = [];
        }
    }
);

const loadSlots = async () => {
    loadingSlots.value = true;
    try {
        const duration = totalDuration.value || 60;
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
    return new Intl.NumberFormat('uz-UZ').format(price);
};

// Step navigation
const canProceedStep1 = computed(() => 
    booking.value.services.length > 0 && 
    booking.value.services.every(s => s.service_id && s.duration_id)
);

const canProceedStep2 = computed(() => 
    booking.value.master_id && booking.value.date && booking.value.slot
);

// Format slot window display (e.g., "09:00" → "09:00–09:30")
const slotDisplay = computed(() => {
    if (!booking.value.slot) return '';
    const [hours, minutes] = booking.value.slot.split(':').map(Number);
    const endMinutes = minutes + 30;
    const endHours = hours + Math.floor(endMinutes / 60);
    const endMins = endMinutes % 60;
    return `${booking.value.slot}–${String(endHours).padStart(2, '0')}:${String(endMins).padStart(2, '0')}`;
});

const canSubmit = computed(() => canProceedStep2.value);

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
        const response = await fetch('/api/public/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                services: booking.value.services.map(sel => ({
                    service_type_id: sel.service_id,
                    duration_id: sel.duration_id,
                })),
                service_type_id: booking.value.services[0]?.service_id,
                duration_id: booking.value.services[0]?.duration_id,
                master_id: booking.value.master_id,
                date: booking.value.date,
                arrival_window_start: booking.value.slot,
                people_count: booking.value.people_count,
                total_duration: totalDuration.value,
                pressure_level: booking.value.pressure_level,
                notes: booking.value.notes,
            }),
        });
        
        const result = await response.json();
        
        if (result.success) {
            router.visit('/booking/success');
        } else {
            submitError.value = result.message || 'Xatolik yuz berdi';
        }
    } catch (e) {
        console.error('Booking failed:', e);
        submitError.value = 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
    }
    submitting.value = false;
};

// Pressure levels
const pressureLevels = [
    { value: 'light', label: 'Yengil', icon: '○' },
    { value: 'medium', label: "O'rtacha", icon: '◐' },
    { value: 'strong', label: 'Kuchli', icon: '●' },
];
</script>

<template>
    <Head title="Buyurtma berish" />
    
    <div class="booking-page">
        <!-- Navigation -->
        <nav class="booking-nav">
            <div class="nav-container">
                <a href="/" class="nav-brand">Home Massage</a>
            </div>
        </nav>

        <!-- Progress Steps -->
        <div class="progress-container">
            <div class="progress-steps">
                <div class="progress-step" :class="{ active: step >= 1, completed: step > 1 }">
                    <span class="step-number">1</span>
                    <span class="step-label">Xizmat</span>
                </div>
                <div class="progress-line" :class="{ active: step > 1 }"></div>
                <div class="progress-step" :class="{ active: step >= 2, completed: step > 2 }">
                    <span class="step-number">2</span>
                    <span class="step-label">Vaqt</span>
                </div>
                <div class="progress-line" :class="{ active: step > 2 }"></div>
                <div class="progress-step" :class="{ active: step >= 3 }">
                    <span class="step-number">3</span>
                    <span class="step-label">Tasdiqlash</span>
                </div>
            </div>
        </div>

        <div class="booking-container">
            <!-- Step 1: Service Selection -->
            <div v-if="step === 1" class="step-content">
                <h2 class="step-title">Xizmatlarni tanlang</h2>
                <p class="step-subtitle">Bir yoki bir nechta xizmatni tanlashingiz mumkin</p>

                <!-- Services Grid -->
                <div class="services-grid">
                    <div 
                        v-for="service in services" 
                        :key="service.id"
                        class="service-card"
                        :class="{ selected: isServiceSelected(service.id) }"
                        @click="toggleService(service.id)"
                    >
                        <div class="service-image" v-if="service.image_url">
                            <img :src="service.image_url" :alt="service.name" />
                        </div>
                        <div class="service-image service-placeholder" v-else>
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                            </svg>
                        </div>
                        <div class="service-info">
                            <span class="service-name">{{ service.name }}</span>
                            <span class="service-price" v-if="service.durations?.[0]">
                                {{ formatPrice(service.durations[0].price) }} so'm dan
                            </span>
                        </div>
                        <div class="service-check" v-if="isServiceSelected(service.id)">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Selected services with duration pickers -->
                <div v-if="booking.services.length > 0" class="selected-services">
                    <h3 class="section-title">Tanlangan xizmatlar</h3>
                    <div 
                        v-for="sel in booking.services" 
                        :key="sel.service_id"
                        class="duration-card"
                    >
                        <div class="duration-header">
                            <span class="duration-service-name">{{ getServiceById(sel.service_id)?.name }}</span>
                            <span v-if="getSelectedDuration(sel.service_id)" class="duration-price">
                                {{ formatPrice(getSelectedDuration(sel.service_id).price) }} so'm
                            </span>
                        </div>
                        <div class="duration-options">
                            <button 
                                v-for="dur in getServiceById(sel.service_id)?.durations" 
                                :key="dur.id"
                                class="duration-btn"
                                :class="{ selected: sel.duration_id === dur.id }"
                                @click="setDuration(sel.service_id, dur.id)"
                            >
                                {{ dur.duration }} min
                            </button>
                        </div>
                    </div>
                </div>

                <!-- People count -->
                <div class="people-section">
                    <h3 class="section-title">Necha kishi?</h3>
                    <div class="people-control">
                        <button class="people-btn" :disabled="booking.people_count <= 1" @click="removePerson">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                        <span class="people-count">{{ booking.people_count }}</span>
                        <button class="people-btn" :disabled="booking.people_count >= 5" @click="addPerson">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Pressure level -->
                <div class="pressure-section">
                    <h3 class="section-title">Bosim kuchi</h3>
                    <div class="pressure-options">
                        <button 
                            v-for="level in pressureLevels" 
                            :key="level.value"
                            class="pressure-btn"
                            :class="{ selected: booking.pressure_level === level.value }"
                            @click="booking.pressure_level = level.value"
                        >
                            <span class="pressure-icon">{{ level.icon }}</span>
                            <span>{{ level.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Summary -->
                <div v-if="canProceedStep1" class="step-summary">
                    <div class="summary-row">
                        <span>{{ booking.services.length }} xizmat, {{ booking.people_count }} kishi</span>
                        <span class="summary-duration">{{ totalDuration }} min</span>
                    </div>
                    <div class="summary-row total">
                        <span>Jami:</span>
                        <span class="summary-price">{{ formatPrice(totalPrice) }} so'm</span>
                    </div>
                </div>
            </div>

            <!-- Step 2: Master & Time Selection -->
            <div v-if="step === 2" class="step-content">
                <h2 class="step-title">Master va vaqtni tanlang</h2>

                <!-- Master selection -->
                <div class="master-section">
                    <h3 class="section-title">Master</h3>
                    <div v-if="filteredMasters.length === 0" class="empty-state">
                        Bu xizmat uchun master topilmadi
                    </div>
                    <div v-else class="masters-grid">
                        <div 
                            v-for="master in filteredMasters" 
                            :key="master.id"
                            class="master-card"
                            :class="{ selected: booking.master_id === master.id }"
                            @click="booking.master_id = master.id"
                        >
                            <div class="master-avatar">
                                <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                                <span v-else>{{ master.name?.charAt(0) }}</span>
                            </div>
                            <span class="master-name">{{ master.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Date selection -->
                <div class="date-section">
                    <h3 class="section-title">Sana</h3>
                    <div class="dates-row">
                        <button 
                            v-for="d in availableDates" 
                            :key="d.value"
                            class="date-btn"
                            :class="{ selected: booking.date === d.value }"
                            @click="booking.date = d.value"
                        >
                            <span class="date-day">{{ d.dayName }}</span>
                            <span class="date-num">{{ d.day }}</span>
                        </button>
                    </div>
                </div>

                <!-- Time slots -->
                <div v-if="booking.master_id && booking.date" class="slots-section">
                    <h3 class="section-title">Kelish oynasi</h3>
                    <div v-if="loadingSlots" class="loading-state">
                        <div class="spinner"></div>
                        <span>Yuklanmoqda...</span>
                    </div>
                    <div v-else-if="availableSlots.length === 0" class="empty-state">
                        Bu kunga bo'sh vaqt yo'q
                    </div>
                    <div v-else class="slots-grid">
                        <button 
                            v-for="slot in availableSlots" 
                            :key="slot.start"
                            class="slot-btn"
                            :class="{ selected: booking.slot === slot.start }"
                            @click="booking.slot = slot.start"
                        >
                            {{ slot.display }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Confirmation -->
            <div v-if="step === 3" class="step-content">
                <h2 class="step-title">Buyurtmani tasdiqlang</h2>

                <!-- Order summary -->
                <div class="confirm-card">
                    <h3 class="confirm-title">Buyurtma ma'lumotlari</h3>
                    
                    <div v-for="sel in booking.services" :key="sel.service_id" class="confirm-row">
                        <span class="confirm-label">{{ getServiceById(sel.service_id)?.name }}:</span>
                        <span class="confirm-value">{{ getSelectedDuration(sel.service_id)?.duration }} min</span>
                    </div>
                    
                    <div class="confirm-row">
                        <span class="confirm-label">Kishi soni:</span>
                        <span class="confirm-value">{{ booking.people_count }}</span>
                    </div>
                    
                    <div class="confirm-divider"></div>
                    
                    <div class="confirm-row">
                        <span class="confirm-label">Master:</span>
                        <span class="confirm-value">{{ selectedMaster?.name }}</span>
                    </div>
                    
                    <div class="confirm-row">
                        <span class="confirm-label">Sana:</span>
                        <span class="confirm-value">{{ booking.date }}</span>
                    </div>
                    
                    <div class="confirm-row">
                        <span class="confirm-label">Kelish oynasi:</span>
                        <span class="confirm-value">{{ slotDisplay }}</span>
                    </div>
                    
                    <div class="confirm-row">
                        <span class="confirm-label">Jami vaqt:</span>
                        <span class="confirm-value">{{ totalDuration }} daqiqa</span>
                    </div>
                    
                    <div class="confirm-row">
                        <span class="confirm-label">Bosim:</span>
                        <span class="confirm-value">{{ pressureLevels.find(p => p.value === booking.pressure_level)?.label }}</span>
                    </div>
                    
                    <div class="confirm-divider"></div>
                    
                    <div class="confirm-row total">
                        <span class="confirm-label">Jami:</span>
                        <span class="confirm-value">{{ formatPrice(totalPrice) }} so'm</span>
                    </div>
                </div>

                <!-- Customer info (logged in user) -->
                <div class="customer-section">
                    <h3 class="section-title">Sizning ma'lumotlaringiz</h3>
                    
                    <div class="customer-info-card">
                        <div class="customer-info-row">
                            <span class="customer-info-label">Ism:</span>
                            <span class="customer-info-value">{{ customer?.name || '-' }}</span>
                        </div>
                        <div class="customer-info-row">
                            <span class="customer-info-label">Telefon:</span>
                            <span class="customer-info-value">{{ customer?.phone || '-' }}</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Qo'shimcha izoh (ixtiyoriy)</label>
                        <textarea 
                            v-model="booking.notes"
                            class="form-textarea"
                            placeholder="Masalan: 5-qavat, 25-xonadon"
                            rows="3"
                        ></textarea>
                    </div>
                </div>

                <!-- Error message -->
                <div v-if="submitError" class="error-message">
                    {{ submitError }}
                </div>
            </div>

            <!-- Navigation buttons -->
            <div class="step-actions">
                <button 
                    v-if="step > 1" 
                    class="btn-secondary"
                    @click="prevStep"
                >
                    Orqaga
                </button>
                <div v-else></div>
                
                <button 
                    v-if="step === 1"
                    class="btn-primary"
                    :disabled="!canProceedStep1"
                    @click="nextStep"
                >
                    Davom etish
                </button>
                <button 
                    v-else-if="step === 2"
                    class="btn-primary"
                    :disabled="!canProceedStep2"
                    @click="nextStep"
                >
                    Davom etish
                </button>
                <button 
                    v-else
                    class="btn-success"
                    :disabled="!canSubmit || submitting"
                    @click="submitBooking"
                >
                    <span v-if="submitting">Yuborilmoqda...</span>
                    <span v-else>Buyurtma berish</span>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Styles loaded from resources/css/public/booking.css via app.css -->
