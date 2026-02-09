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

// Service selection
const toggleService = (serviceId) => {
    const index = booking.value.services.findIndex(s => s.service_id === serviceId);
    if (index >= 0) {
        booking.value.services.splice(index, 1);
    } else {
        if (booking.value.services.length < booking.value.people_count) {
            const service = props.services?.find(s => s.id === serviceId);
            const defaultDuration = service?.durations?.find(d => d.is_default) || service?.durations?.[0];
            booking.value.services.push({
                service_id: serviceId,
                duration_id: defaultDuration?.id || null,
            });
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
        const response = await fetch('/api/miniapp/orders', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                services: booking.value.services,
                master_ids: booking.value.master_ids,
                master_id: booking.value.master_ids[0],
                booking_date: booking.value.date,
                arrival_window_start: booking.value.slot,
                pressure_level: booking.value.pressure_level,
                people_count: booking.value.people_count,
                notes: booking.value.notes,
            }),
        });
        
        const data = await response.json();
        
        if (data.success && data.data?.order) {
            router.visit(`/app/booking/success?order=${data.data.order.id}`);
        } else {
            submitError.value = data.message || 'Xatolik yuz berdi';
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

// Selected service summary for bottom bar
const selectedServiceSummary = computed(() => {
    if (booking.value.services.length === 0) return '';
    const firstService = getServiceById(booking.value.services[0]?.service_id);
    const firstDuration = getSelectedDuration(booking.value.services[0]?.service_id);
    return `${firstService?.name}, ${firstDuration?.duration || 60} daq`;
});
</script>

<template>
    <div class="bk-page">
        <!-- Header -->
        <header class="bk-header">
            <button class="bk-back" @click="step > 1 ? prevStep() : router.visit('/app')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            <!-- Service Cards -->
            <div class="service-list">
                <div 
                    v-for="service in services" 
                    :key="service.id"
                    class="service-card"
                    :class="{ selected: isServiceSelected(service.id) }"
                    @click="toggleService(service.id)"
                >
                    <div class="service-img">
                        <img v-if="service.image_url" :src="service.image_url" :alt="service.name" />
                        <div v-else class="service-img-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
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
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Duration Selection (for selected service) -->
            <div v-if="booking.services.length > 0" class="section">
                <h3 class="section-title">Davomiyligi</h3>
                <div class="chip-row">
                    <button 
                        v-for="dur in getServiceById(booking.services[0]?.service_id)?.durations" 
                        :key="dur.id"
                        class="chip"
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
                        class="chip"
                        :class="{ selected: booking.pressure_level === level.value }"
                        @click="booking.pressure_level = level.value"
                    >
                        {{ level.label }}
                    </button>
                </div>
            </div>

            <!-- People Count -->
            <div class="section">
                <h3 class="section-title">Kishilar soni</h3>
                <div class="people-control">
                    <button class="people-btn" :disabled="booking.people_count <= 1" @click="removePerson">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <span class="people-num">{{ booking.people_count }} kishi</span>
                    <button class="people-btn" :disabled="booking.people_count >= 5" @click="addPerson">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
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
                        class="date-chip"
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
                        class="slot-chip"
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
                        class="master-card"
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
            <div class="confirm-box">
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
                    class="notes-input"
                    placeholder="Masalan: 5-qavat, 25-xonadon"
                    rows="3"
                ></textarea>
            </div>

            <div v-if="submitError" class="error-msg">{{ submitError }}</div>
        </div>

        <!-- Bottom Bar -->
        <div class="bk-bottom">
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
                class="bk-btn confirm"
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
    background: #F8F6F3;
    padding-bottom: 140px;
}

/* Header */
.bk-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #fff;
    border-bottom: 1px solid #E5E5E5;
    position: sticky;
    top: 0;
    z-index: 100;
}

.bk-back {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F5F5F5;
    border: none;
    border-radius: 12px;
    color: #333;
    cursor: pointer;
}

.bk-title {
    flex: 1;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.bk-step {
    font-size: 14px;
    color: #999;
}

/* Content */
.bk-content {
    padding: 16px;
}

/* Section */
.section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #666;
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
    padding: 12px;
    background: #fff;
    border: 2px solid transparent;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.service-card.selected {
    border-color: #B8A369;
    background: #FFFDF8;
}

.service-img {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    background: #F5F5F5;
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
    color: #CCC;
}

.service-info {
    flex: 1;
    min-width: 0;
}

.service-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 4px;
}

.service-desc {
    font-size: 13px;
    color: #888;
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
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #B8A369;
    border-radius: 50%;
    color: #fff;
}

/* Chips */
.chip-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.chip {
    padding: 10px 20px;
    background: #fff;
    border: 2px solid #E5E5E5;
    border-radius: 24px;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
}

.chip.selected {
    border-color: #B8A369;
    background: #B8A369;
    color: #fff;
}

/* People Control */
.people-control {
    display: flex;
    align-items: center;
    gap: 24px;
    justify-content: center;
    padding: 16px;
    background: #fff;
    border-radius: 16px;
}

.people-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F5F5F5;
    border: 1px solid #E5E5E5;
    border-radius: 50%;
    color: #333;
    cursor: pointer;
}

.people-btn:disabled {
    opacity: 0.3;
}

.people-num {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    min-width: 80px;
    text-align: center;
}

/* Date Selection */
.date-row {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}

.date-row::-webkit-scrollbar {
    display: none;
}

.date-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 16px;
    background: #fff;
    border: 2px solid #E5E5E5;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 64px;
}

.date-chip.selected {
    border-color: #B8A369;
    background: #B8A369;
}

.date-day {
    font-size: 11px;
    color: #888;
    text-transform: uppercase;
}

.date-chip.selected .date-day {
    color: rgba(255,255,255,0.8);
}

.date-num {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-top: 2px;
}

.date-chip.selected .date-num {
    color: #fff;
}

/* Slots */
.slots-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.slot-chip {
    padding: 12px 8px;
    background: #fff;
    border: 2px solid #E5E5E5;
    border-radius: 12px;
    font-size: 13px;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
}

.slot-chip.selected {
    border-color: #B8A369;
    background: #B8A369;
    color: #fff;
}

/* Master List */
.master-list {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}

.master-list::-webkit-scrollbar {
    display: none;
}

.master-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px;
    background: #fff;
    border: 2px solid transparent;
    border-radius: 16px;
    min-width: 100px;
    transition: all 0.2s;
}

.master-card.selected {
    border-color: #B8A369;
}

.master-photo {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    display: flex;
    align-items: center;
    justify-content: center;
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
    color: #333;
}

.master-exp {
    font-size: 12px;
    color: #888;
    background: #F5F5F5;
    padding: 2px 8px;
    border-radius: 10px;
}

.master-btn {
    padding: 8px 16px;
    background: #B8A369;
    border: none;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #fff;
    cursor: pointer;
}

.master-btn.active {
    background: #8B7B4D;
}

/* Confirmation */
.confirm-box {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
}

.confirm-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #E5E5E5;
}

.confirm-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.confirm-label {
    font-size: 14px;
    color: #888;
}

.confirm-value {
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

.confirm-total {
    display: flex;
    justify-content: space-between;
    padding-top: 12px;
    margin-top: 12px;
    border-top: 1px solid #E5E5E5;
}

.confirm-price {
    font-size: 18px;
    font-weight: 700;
    color: #B8A369;
}

/* Notes */
.notes-input {
    width: 100%;
    padding: 16px;
    background: #fff;
    border: 1px solid #E5E5E5;
    border-radius: 12px;
    font-size: 14px;
    color: #333;
    resize: none;
    outline: none;
}

.notes-input:focus {
    border-color: #B8A369;
}

/* Empty/Loading states */
.empty-hint {
    padding: 24px;
    text-align: center;
    color: #999;
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
    border: 3px solid #E5E5E5;
    border-top-color: #B8A369;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Error */
.error-msg {
    padding: 12px 16px;
    background: #FEE2E2;
    border-radius: 12px;
    color: #DC2626;
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
    background: #fff;
    border-top: 1px solid #E5E5E5;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
}

.bottom-summary {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #E5E5E5;
}

.summary-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.summary-label {
    font-size: 12px;
    color: #888;
}

.summary-value {
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

.summary-detail {
    font-size: 12px;
    color: #888;
}

.summary-price {
    text-align: right;
}

.price-label {
    font-size: 12px;
    color: #888;
    display: block;
}

.price-value {
    font-size: 16px;
    font-weight: 700;
    color: #B8A369;
}

.bk-btn {
    width: 100%;
    padding: 16px;
    background: #B8A369;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.bk-btn.confirm {
    background: #B8A369;
}
</style>
