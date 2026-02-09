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
const step = ref(1);

// Booking data
const booking = ref({
    services: [],
    people_count: 1,
    master_ids: [],
    date: null,
    slot: null,
    pressure_level: 'medium',
    notes: '',
});

// People count
const addPerson = () => {
    if (booking.value.people_count < 5) booking.value.people_count++;
};

const removePerson = () => {
    if (booking.value.people_count > 1) {
        booking.value.people_count--;
        while (booking.value.services.length > booking.value.people_count) {
            booking.value.services.pop();
        }
        while (booking.value.master_ids.length > booking.value.people_count) {
            booking.value.master_ids.pop();
        }
    }
};

// Service selection (radio behavior for 1 person, multi-select for 2+)
const toggleService = (serviceId) => {
    const service = props.services?.find(s => s.id === serviceId);
    const defaultDuration = service?.durations?.find(d => d.is_default) || service?.durations?.[0];
    
    if (booking.value.people_count === 1) {
        // Radio button behavior - replace selection
        booking.value.services = [{
            service_id: serviceId,
            duration_id: defaultDuration?.id || null,
        }];
    } else {
        // Multi-select behavior
        const index = booking.value.services.findIndex(s => s.service_id === serviceId);
        if (index >= 0) {
            booking.value.services.splice(index, 1);
        } else {
            if (booking.value.services.length < booking.value.people_count) {
                booking.value.services.push({
                    service_id: serviceId,
                    duration_id: defaultDuration?.id || null,
                });
            }
        }
    }
};

const isServiceSelected = (serviceId) => booking.value.services.some(s => s.service_id === serviceId);
const getServiceById = (serviceId) => props.services?.find(s => s.id === serviceId);

const getSelectedDuration = (serviceId) => {
    const sel = booking.value.services.find(s => s.service_id === serviceId);
    const service = getServiceById(serviceId);
    return service?.durations?.find(d => d.id === sel?.duration_id);
};

const setDuration = (serviceId, durationId) => {
    const sel = booking.value.services.find(s => s.service_id === serviceId);
    if (sel) sel.duration_id = durationId;
};

// Computed
const totalPrice = computed(() => {
    return booking.value.services.reduce((sum, sel) => {
        const duration = getSelectedDuration(sel.service_id);
        return sum + (duration?.price || 0);
    }, 0);
});

const totalDuration = computed(() => {
    const baseTotal = booking.value.services.reduce((sum, sel) => {
        const duration = getSelectedDuration(sel.service_id);
        return sum + (duration?.duration || 0);
    }, 0);
    const buffer = 10;
    const peopleCount = booking.value.services.length || 1;
    return baseTotal + buffer * (peopleCount - 1);
});

const selectedServiceIds = computed(() => booking.value.services.map(s => s.service_id));

const selectedMasters = computed(() => 
    booking.value.master_ids.map(id => props.masters?.find(m => m.id === id)).filter(Boolean)
);

const filteredMasters = computed(() => {
    if (selectedServiceIds.value.length === 0) return props.masters || [];
    return (props.masters || []).filter(m => 
        selectedServiceIds.value.every(serviceId => m.service_type_ids?.includes(serviceId))
    );
});

// Master selection
const toggleMaster = (masterId) => {
    const index = booking.value.master_ids.indexOf(masterId);
    if (index >= 0) {
        booking.value.master_ids.splice(index, 1);
    } else {
        if (booking.value.master_ids.length < booking.value.people_count) {
            booking.value.master_ids.push(masterId);
        }
    }
    booking.value.slot = null;
};

const isMasterSelected = (masterId) => booking.value.master_ids.includes(masterId);

// Available dates (7 days)
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
        });
    }
    return dates;
});

// Slots
const availableSlots = ref([]);
const loadingSlots = ref(false);

const loadSlots = async () => {
    if (!booking.value.date || booking.value.master_ids.length !== booking.value.people_count) {
        availableSlots.value = [];
        return;
    }
    
    loadingSlots.value = true;
    try {
        const duration = getSelectedDuration(booking.value.services[0]?.service_id)?.duration || 60;
        const masterIds = booking.value.master_ids.join(',');
        const response = await fetch(`/api/slots/multi-master?date=${booking.value.date}&duration=${duration}&master_ids=${masterIds}`);
        const data = await response.json();
        availableSlots.value = data.data?.slots || data.slots || [];
    } catch (e) {
        console.error('Failed to load slots:', e);
    }
    loadingSlots.value = false;
};

watch([() => booking.value.date, () => booking.value.master_ids.length], () => {
    booking.value.slot = null;
    loadSlots();
});

// Format
const formatPrice = (price) => new Intl.NumberFormat('uz-UZ').format(price);

// Step navigation
const canProceedStep1 = computed(() => 
    booking.value.services.length === booking.value.people_count && 
    booking.value.services.every(s => s.service_id && s.duration_id)
);

const canProceedStep2 = computed(() => 
    booking.value.master_ids.length === booking.value.people_count && 
    booking.value.date && 
    booking.value.slot
);

const slotDisplay = computed(() => {
    if (!booking.value.slot) return '';
    const [hours, minutes] = booking.value.slot.split(':').map(Number);
    const endMinutes = minutes + 30;
    const endHours = hours + Math.floor(endMinutes / 60);
    const endMins = endMinutes % 60;
    return `${booking.value.slot}–${String(endHours).padStart(2, '0')}:${String(endMins).padStart(2, '0')}`;
});

const nextStep = () => { if (step.value < 3) step.value++; };
const prevStep = () => { if (step.value > 1) step.value--; };

// Submit
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
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                master_id: booking.value.master_ids[0],
                service_type_id: booking.value.services[0]?.service_id,
                duration_id: booking.value.services[0]?.duration_id,
                date: booking.value.date,
                arrival_window_start: booking.value.slot,
                pressure_level: booking.value.pressure_level,
                people_count: booking.value.people_count,
                notes: booking.value.notes,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            router.visit(`/app/booking-success?order_number=${data.data?.order_number}`);
        } else {
            submitError.value = data.message || data.errors?.[Object.keys(data.errors)[0]]?.[0] || 'Xatolik yuz berdi';
        }
    } catch (e) {
        console.error('Booking failed:', e);
        submitError.value = 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
    }
    submitting.value = false;
};

// Pressure levels
const pressureLevels = [
    { value: 'light', label: 'Yengil' },
    { value: 'medium', label: "O'rtacha" },
    { value: 'strong', label: 'Kuchli' },
    { value: 'any', label: "Farqi yo'q" },
];

const selectedServiceSummary = computed(() => {
    if (booking.value.services.length === 0) return '';
    const firstService = getServiceById(booking.value.services[0]?.service_id);
    const firstDuration = getSelectedDuration(booking.value.services[0]?.service_id);
    return `${firstService?.name}, ${firstDuration?.duration || 60} daq`;
});
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
            <button class="bk-back glass-btn" @click="step > 1 ? prevStep() : router.visit('/app')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <h1 class="bk-title">
                <span v-if="step === 1">Xizmat tanlang</span>
                <span v-else-if="step === 2">Vaqt va usta tanlang</span>
                <span v-else>Tasdiqlash</span>
            </h1>
            <span class="bk-step">{{ step }}/3</span>
        </header>

        <!-- Step 1: Service Selection -->
        <div v-if="step === 1" class="bk-content">
            <!-- People Count (at top) -->
            <div class="section">
                <h3 class="section-title">Kishilar soni</h3>
                <div class="people-control glass">
                    <button class="people-btn glass-btn" :disabled="booking.people_count <= 1" @click="removePerson">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <span class="people-num">{{ booking.people_count }} kishi</span>
                    <button class="people-btn glass-btn" :disabled="booking.people_count >= 5" @click="addPerson">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                </div>
            </div>

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
                        <img v-if="service.image_url" :src="service.image_url" :alt="service.name" />
                        <div v-else class="service-img-placeholder">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="service-info">
                        <h3 class="service-name">{{ service.name }}</h3>
                        <p class="service-desc">{{ service.description }}</p>
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
            <div v-if="booking.services.length > 0" class="section">
                <h3 class="section-title">Davomiyligi</h3>
                <div class="chip-row">
                    <button 
                        v-for="dur in getServiceById(booking.services[0]?.service_id)?.durations" 
                        :key="dur.id"
                        class="chip glass"
                        :class="{ selected: booking.services[0]?.duration_id === dur.id }"
                        @click="setDuration(booking.services[0].service_id, dur.id)"
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
                <div v-if="!booking.date" class="empty-hint">Avval sanani tanlang</div>
                <div v-else-if="booking.master_ids.length !== booking.people_count" class="empty-hint">Avval ustani tanlang</div>
                <div v-else-if="loadingSlots" class="loading">
                    <div class="spinner"></div>
                </div>
                <div v-else-if="availableSlots.length === 0" class="empty-hint">Bu kunga vaqt yo'q</div>
                <div v-else class="slots-grid">
                    <button 
                        v-for="slot in availableSlots" 
                        :key="slot.start"
                        class="slot-chip glass"
                        :class="{ selected: booking.slot === slot.start }"
                        @click="booking.slot = slot.start"
                    >
                        {{ slot.display }}
                    </button>
                </div>
            </div>

            <!-- Master Selection -->
            <div class="section">
                <h3 class="section-title">Ustani tanlang</h3>
                <div v-if="filteredMasters.length === 0" class="empty-hint">Bu xizmat uchun usta topilmadi</div>
                <div v-else class="master-list">
                    <div 
                        v-for="master in filteredMasters" 
                        :key="master.id"
                        class="master-card glass"
                        :class="{ selected: isMasterSelected(master.id) }"
                    >
                        <div class="master-photo">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <span v-else class="master-initial">{{ master.name?.charAt(0) }}</span>
                        </div>
                        <span class="master-name">{{ master.name }}</span>
                        <span class="master-exp">{{ master.experience || 3 }} yil</span>
                        <button 
                            class="master-btn"
                            :class="{ active: isMasterSelected(master.id) }"
                            @click="toggleMaster(master.id)"
                        >
                            {{ isMasterSelected(master.id) ? 'Tanlangan' : 'Tanlash' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Confirmation -->
        <div v-if="step === 3" class="bk-content">
            <div class="confirm-box glass">
                <h3 class="confirm-title">Buyurtma tafsilotlari</h3>
                
                <div class="confirm-row">
                    <span class="confirm-label">Sana:</span>
                    <span class="confirm-value">{{ booking.date }}</span>
                </div>
                
                <div class="confirm-row">
                    <span class="confirm-label">Vaqt:</span>
                    <span class="confirm-value">{{ slotDisplay }}</span>
                </div>
                
                <div class="confirm-row">
                    <span class="confirm-label">Usta:</span>
                    <span class="confirm-value">{{ selectedMasters.map(m => m.name).join(', ') }}</span>
                </div>
                
                <div class="confirm-row">
                    <span class="confirm-label">Xizmat:</span>
                    <span class="confirm-value">{{ selectedServiceSummary }}</span>
                </div>
                
                <div class="confirm-total">
                    <span class="confirm-label">Jami:</span>
                    <span class="confirm-price">{{ formatPrice(totalPrice) }} UZS</span>
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

        <!-- Bottom Bar -->
        <div class="bk-bottom glass">
            <div v-if="step === 1 && booking.services.length > 0" class="bottom-summary">
                <div class="summary-text">
                    <span class="summary-label">Tanlangan:</span>
                    <span class="summary-value">{{ selectedServiceSummary }}</span>
                </div>
                <div class="summary-price">
                    <span class="price-label">Narxi:</span>
                    <span class="price-value">{{ formatPrice(totalPrice) }} UZS</span>
                </div>
            </div>
            
            <div v-if="step === 2 && booking.slot" class="bottom-summary">
                <div class="summary-text">
                    <span class="summary-label">Tanlangan:</span>
                    <span class="summary-value">{{ selectedServiceSummary }}</span>
                    <span class="summary-detail">{{ slotDisplay }}, {{ selectedMasters[0]?.name }}</span>
                </div>
                <div class="summary-price">
                    <span class="price-label">Narxi:</span>
                    <span class="price-value">{{ formatPrice(totalPrice) }} UZS</span>
                </div>
            </div>

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
                v-else
                class="bk-btn"
                :disabled="submitting"
                @click="submitBooking"
            >
                {{ submitting ? 'Yuborilmoqda...' : 'Buyurtma berish' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Base */
.bk-page {
    min-height: 100vh;
    padding-bottom: 160px;
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

.bk-step {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
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

/* People Control */
.people-control {
    display: flex;
    align-items: center;
    gap: 24px;
    justify-content: center;
    padding: 16px;
    border-radius: 20px;
}

.people-btn {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.people-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.people-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.people-num {
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    min-width: 80px;
    text-align: center;
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

.bk-btn {
    width: 100%;
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
</style>
