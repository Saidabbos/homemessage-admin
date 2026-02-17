<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const page = usePage();

const props = defineProps({
    services: Array,
    masters: Array,
    payment: Object,
});

// Wizard state: 1 | 2 | 3 | 'cart' | 'payment'
const step = ref(1);

// Single service selection
const selectedService = ref(null);

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

// ==================== Address ====================

const savedAddresses = ref([]);
const selectedAddressId = ref(null);
const showManualAddress = ref(false);
const manualAddress = ref({
    address: '',
    entrance: '',
    floor: '',
    apartment: '',
    landmark: '',
});

const loadAddresses = async () => {
    try {
        const response = await fetch('/api/user/addresses');
        const data = await response.json();
        savedAddresses.value = data.addresses || [];
        
        // Auto-select default address
        const defaultAddr = savedAddresses.value.find(a => a.is_default);
        if (defaultAddr) {
            selectedAddressId.value = defaultAddr.id;
        } else if (savedAddresses.value.length > 0) {
            selectedAddressId.value = savedAddresses.value[0].id;
        }
    } catch (e) {
        console.error('Failed to load addresses:', e);
    }
};

const selectedAddress = computed(() => 
    savedAddresses.value.find(a => a.id === selectedAddressId.value)
);

const addressForSubmit = computed(() => {
    if (showManualAddress.value) {
        return manualAddress.value;
    }
    if (selectedAddress.value) {
        return {
            address: selectedAddress.value.address,
            entrance: selectedAddress.value.entrance || '',
            floor: selectedAddress.value.floor || '',
            apartment: selectedAddress.value.apartment || '',
            landmark: selectedAddress.value.landmark || '',
        };
    }
    return null;
});

const hasValidAddress = computed(() => {
    if (showManualAddress.value) {
        return manualAddress.value.address.trim().length > 5;
    }
    return selectedAddressId.value !== null;
});

onMounted(() => {
    loadAddresses();
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

const getServiceIcon = (service) => {
    const name = getTranslated(service?.name).toLowerCase();
    if (name.includes('classic') || name.includes('klassik')) return 'classic';
    if (name.includes('sport')) return 'sport';
    if (name.includes('relax') || name.includes('rileks')) return 'relax';
    if (name.includes('aroma')) return 'aroma';
    return 'classic';
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

const masterSearch = ref('');

const filteredMasters = computed(() => {
    let masters = props.masters || [];
    
    if (selectedServiceIds.value.length > 0) {
        masters = masters.filter(m =>
            selectedServiceIds.value.some(serviceId =>
                (m.service_type_ids || []).includes(serviceId)
            )
        );
    }
    
    if (masterSearch.value.trim()) {
        const q = masterSearch.value.toLowerCase().trim();
        masters = masters.filter(m =>
            (m.name || '').toLowerCase().includes(q) ||
            (m.first_name || '').toLowerCase().includes(q)
        );
    }
    
    return masters;
});

watch(() => selectedService.value?.service_id, () => {
    booking.value.master_id = null;
    booking.value.date = null;
    booking.value.slot = null;
});

// ==================== Dates & Slots ====================

const dayNames = ['Yak', 'Dush', 'Sesh', 'Chor', 'Pay', 'Jum', 'Shan'];
const monthNames = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyn', 'Iyl', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];

const availableDates = computed(() => {
    const dates = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date();
        d.setDate(d.getDate() + i);
        dates.push({
            value: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`,
            dayName: dayNames[d.getDay()],
            day: d.getDate(),
            month: monthNames[d.getMonth()],
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

// ==================== Grouped Slots ====================

const getSlotPeriod = (time) => {
    const hour = parseInt(time.split(':')[0], 10);
    if (hour < 12) return 'morning';
    if (hour < 17) return 'day';
    return 'evening';
};

const periodLabels = { morning: 'Ertalab', day: 'Kunduzi', evening: 'Kechqurun' };
const periodIcons = { morning: 'sun-rise', day: 'sun', evening: 'moon' };

const groupedSlots = computed(() => {
    const groups = { morning: [], day: [], evening: [] };
    for (const slot of availableSlots.value) {
        groups[getSlotPeriod(slot.start)].push(slot);
    }
    return Object.entries(groups)
        .filter(([_, slots]) => slots.length > 0)
        .map(([period, slots]) => ({ period, label: periodLabels[period], icon: periodIcons[period], slots }));
});

// ==================== Formatting ====================

const formatPrice = (price) => new Intl.NumberFormat('uz-UZ').format(Number(price) || 0);

const formatSlotRange = (start) => {
    if (!start) return '';
    const [h, m] = start.split(':').map(Number);
    const endM = m + 30;
    const endH = h + Math.floor(endM / 60);
    return `${start}–${String(endH).padStart(2, '0')}:${String(endM % 60).padStart(2, '0')}`;
};

const slotDisplay = computed(() => formatSlotRange(booking.value.slot));

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

// ==================== Navigation ====================

const canProceedStep1 = computed(() =>
    selectedService.value?.service_id && selectedService.value?.duration_id
);

const canProceedStep2 = computed(() =>
    booking.value.master_id && booking.value.date && booking.value.slot
);

const nextStep = () => { if (step.value < 3) step.value++; };
const prevStep = () => {
    if (step.value === 'payment') step.value = 'cart';
    else if (step.value === 'cart') step.value = 3;
    else if (step.value > 1) step.value--;
    else router.visit('/app');
};

const goBack = () => prevStep();

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
    if (cart.value.length === 0) resetWizard();
};

const bookAnotherMaster = () => resetWizard();

const resetWizard = () => {
    selectedService.value = null;
    booking.value = { master_id: null, date: null, slot: null, pressure_level: 'medium', notes: '' };
    step.value = 1;
};

// ==================== Submit ====================

const submitting = ref(false);
const submitError = ref(null);
const createdGroupId = ref(null);

const submitCart = async () => {
    if (cart.value.length === 0) return;
    if (!hasValidAddress.value) {
        submitError.value = 'Manzilni kiriting';
        return;
    }
    
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

        const addr = addressForSubmit.value;
        const response = await fetch('/api/public/orders/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ 
                orders,
                address: addr.address,
                entrance: addr.entrance || null,
                floor: addr.floor || null,
                apartment: addr.apartment || null,
                landmark: addr.landmark || null,
            }),
        });

        const data = await response.json();
        if (data.success) {
            createdGroupId.value = data.group_id;
            // If payment is enabled, go to payment step
            if (props.payment?.enabled) {
                step.value = 'payment';
            } else {
                // Redirect to success page
                router.visit(`/app/booking-success?group_id=${data.group_id || ''}`);
            }
        } else {
            submitError.value = data.message || 'Xatolik yuz berdi';
        }
    } catch (e) {
        submitError.value = 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
    }
    submitting.value = false;
};

// ==================== Payment ====================

const payLater = () => {
    router.visit('/app/orders');
};

const payWithPayme = () => {
    window.location.href = `/booking/payment/${createdGroupId.value}?provider=payme&source=miniapp`;
};

const payWithClick = () => {
    window.location.href = `/booking/payment/${createdGroupId.value}?provider=click&source=miniapp`;
};

// ==================== Constants ====================

const pressureLevels = [
    { value: 'soft', label: 'Yengil', icon: 'feather' },
    { value: 'medium', label: "O'rtacha", icon: 'waves' },
    { value: 'hard', label: 'Kuchli', icon: 'zap' },
];
</script>

<template>
    <div class="bk-page">
        <!-- Header -->
        <header class="bk-header">
            <button class="bk-back-btn" @click="goBack">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>

            <!-- Stepper -->
            <div v-if="step !== 'cart' && step !== 'payment'" class="bk-stepper">
                <div class="bk-step" :class="{ active: step >= 1, done: step > 1 }">
                    <span class="bk-step-circle">
                        <svg v-if="step > 1" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                        <span v-else>1</span>
                    </span>
                    <span class="bk-step-label">Xizmat</span>
                </div>
                <div class="bk-step-line" :class="{ active: step > 1 }"></div>
                <div class="bk-step" :class="{ active: step >= 2, done: step > 2 }">
                    <span class="bk-step-circle">
                        <svg v-if="step > 2" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                        <span v-else>2</span>
                    </span>
                    <span class="bk-step-label">Vaqt</span>
                </div>
                <div class="bk-step-line" :class="{ active: step > 2 }"></div>
                <div class="bk-step" :class="{ active: step >= 3 }">
                    <span class="bk-step-circle">3</span>
                    <span class="bk-step-label">Tasdiqlash</span>
                </div>
            </div>

            <span v-else-if="step === 'cart'" class="bk-header-title">Savat</span>
            <span v-else class="bk-header-title">To'lov</span>

            <!-- Cart Button -->
            <button v-if="step !== 'cart' && step !== 'payment' && cartItemCount > 0" class="bk-cart-btn" @click="step = 'cart'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
                <span class="bk-cart-badge">{{ cartItemCount }}</span>
            </button>
            <div v-else class="bk-header-space"></div>
        </header>

        <!-- Step 1: Service Selection -->
        <div v-if="step === 1" class="bk-content">
            <!-- Services -->
            <section class="bk-section">
                <h2 class="bk-section-title">Massaj turi</h2>
                <div class="bk-service-grid">
                    <div
                        v-for="service in services"
                        :key="service.id"
                        class="bk-service-card"
                        :class="{ selected: isServiceSelected(service.id) }"
                        @click="toggleService(service.id)"
                    >
                        <div class="bk-service-icon">
                            <svg v-if="getServiceIcon(service) === 'classic'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                            <svg v-else-if="getServiceIcon(service) === 'sport'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/></svg>
                            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <span class="bk-service-name">{{ getTranslated(service.name) }}</span>
                        <div v-if="isServiceSelected(service.id)" class="bk-service-check">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Duration -->
            <section v-if="selectedService" class="bk-section">
                <h2 class="bk-section-title">Davomiylik</h2>
                <div class="bk-chip-row">
                    <button
                        v-for="dur in getServiceById(selectedService.service_id)?.durations"
                        :key="dur.id"
                        class="bk-chip"
                        :class="{ selected: selectedService.duration_id === dur.id }"
                        @click="setDuration(selectedService.service_id, dur.id)"
                    >
                        {{ dur.duration }} min · {{ formatPrice(dur.price) }}
                    </button>
                </div>
            </section>

            <!-- Pressure Level -->
            <section class="bk-section">
                <h2 class="bk-section-title">Bosim kuchi</h2>
                <div class="bk-pressure-grid">
                    <button
                        v-for="level in pressureLevels"
                        :key="level.value"
                        class="bk-pressure-card"
                        :class="{ selected: booking.pressure_level === level.value }"
                        @click="booking.pressure_level = level.value"
                    >
                        <div class="bk-pressure-icon">
                            <svg v-if="level.icon === 'feather'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.24 12.24a6 6 0 00-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/></svg>
                            <svg v-else-if="level.icon === 'waves'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/></svg>
                            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <span>{{ level.label }}</span>
                    </button>
                </div>
            </section>
        </div>

        <!-- Step 2: Master & Time -->
        <div v-if="step === 2" class="bk-content">
            <!-- Masters -->
            <section class="bk-section">
                <h2 class="bk-section-title">Master tanlang</h2>
                <div class="bk-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input v-model="masterSearch" type="text" placeholder="Ism bo'yicha qidirish..." />
                </div>
                
                <div v-if="filteredMasters.length === 0" class="bk-empty">Bu xizmat uchun master topilmadi</div>
                <div v-else class="bk-master-list">
                    <div
                        v-for="master in filteredMasters"
                        :key="master.id"
                        class="bk-master-card"
                        :class="{ selected: booking.master_id === master.id }"
                        @click="booking.master_id = master.id"
                    >
                        <div class="bk-master-photo">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <span v-else>{{ master.name?.charAt(0) }}</span>
                        </div>
                        <div class="bk-master-info">
                            <span class="bk-master-name">{{ master.name }}</span>
                            <div class="bk-master-rating">
                                <svg v-for="s in 5" :key="s" width="12" height="12" viewBox="0 0 24 24" fill="#C8A951"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
                                <span>{{ master.rating || '5.0' }}</span>
                            </div>
                        </div>
                        <div v-if="booking.master_id === master.id" class="bk-master-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Date -->
            <section class="bk-section">
                <h2 class="bk-section-title">Sana</h2>
                <div class="bk-date-row">
                    <button
                        v-for="d in availableDates"
                        :key="d.value"
                        class="bk-date-card"
                        :class="{ selected: booking.date === d.value }"
                        @click="booking.date = d.value"
                    >
                        <span class="bk-date-day">{{ d.dayName }}</span>
                        <span class="bk-date-num">{{ d.day }}</span>
                        <span class="bk-date-month">{{ d.month }}</span>
                    </button>
                </div>
            </section>

            <!-- Slots -->
            <section class="bk-section">
                <h2 class="bk-section-title">Vaqt oynasi</h2>
                <div v-if="!booking.master_id" class="bk-empty">Avval masterni tanlang</div>
                <div v-else-if="!booking.date" class="bk-empty">Avval sanani tanlang</div>
                <div v-else-if="loadingSlots" class="bk-loading">
                    <div class="bk-spinner"></div>
                </div>
                <div v-else-if="availableSlots.length === 0" class="bk-empty">Bu kunga bo'sh vaqt yo'q</div>
                <div v-else class="bk-slots-grouped">
                    <div v-for="group in groupedSlots" :key="group.period" class="bk-slot-group">
                        <div class="bk-slot-group-header">
                            <svg v-if="group.icon === 'sun-rise'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="2" x2="12" y2="9"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/><line x1="23" y1="22" x2="1" y2="22"/></svg>
                            <svg v-else-if="group.icon === 'sun'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
                            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                            <span>{{ group.label }}</span>
                        </div>
                        <div class="bk-slot-grid">
                            <button
                                v-for="slot in group.slots"
                                :key="slot.start"
                                class="bk-slot-btn"
                                :class="{ selected: booking.slot === slot.start, disabled: slot.disabled }"
                                :disabled="slot.disabled"
                                @click="booking.slot = slot.start"
                            >
                                {{ formatSlotRange(slot.start) }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Step 3: Confirmation -->
        <div v-if="step === 3" class="bk-content">
            <section class="bk-section">
                <h2 class="bk-section-title">Buyurtma ma'lumotlari</h2>
                <div class="bk-confirm-card">
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Xizmat</span>
                        <span class="bk-confirm-value">{{ getTranslated(getServiceById(selectedService?.service_id)?.name) }}</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Davomiylik</span>
                        <span class="bk-confirm-value">{{ currentItemDuration }} daqiqa</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Master</span>
                        <span class="bk-confirm-value">{{ selectedMaster?.name }}</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Sana</span>
                        <span class="bk-confirm-value">{{ selectedDateLabel }}</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Kelish oynasi</span>
                        <span class="bk-confirm-value">{{ slotDisplay }}</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Bosim</span>
                        <span class="bk-confirm-value">{{ pressureLevels.find(p => p.value === booking.pressure_level)?.label }}</span>
                    </div>
                    <div class="bk-confirm-divider"></div>
                    <div class="bk-confirm-row bk-confirm-total">
                        <span class="bk-confirm-label">Narxi</span>
                        <span class="bk-confirm-value">{{ formatPrice(currentItemPrice) }} so'm</span>
                    </div>
                </div>
            </section>

            <section class="bk-section">
                <h2 class="bk-section-title">Izoh (ixtiyoriy)</h2>
                <textarea
                    v-model="booking.notes"
                    class="bk-textarea"
                    rows="3"
                    placeholder="Maxsus talablar yoki izohlar..."
                ></textarea>
            </section>
        </div>

        <!-- Cart View -->
        <div v-if="step === 'cart'" class="bk-content">
            <div v-if="cart.length === 0" class="bk-cart-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
                <p>Savat bo'sh</p>
                <button class="bk-btn-primary" @click="resetWizard">Xizmat tanlash</button>
            </div>

            <div v-else>
                <div class="bk-cart-items">
                    <div v-for="item in cart" :key="item.id" class="bk-cart-item">
                        <div class="bk-cart-item-photo">
                            <img v-if="item.master_photo" :src="item.master_photo" :alt="item.master_name" />
                            <span v-else>{{ item.master_name?.charAt(0) }}</span>
                        </div>
                        <div class="bk-cart-item-info">
                            <span class="bk-cart-item-service">{{ item.service_name }}</span>
                            <span class="bk-cart-item-meta">{{ item.master_name }} · {{ item.duration_minutes }} min</span>
                            <span class="bk-cart-item-time">{{ item.date_label }} · {{ item.slot_display }}</span>
                        </div>
                        <div class="bk-cart-item-right">
                            <span class="bk-cart-item-price">{{ formatPrice(item.price) }}</span>
                            <button class="bk-cart-remove" @click="removeFromCart(item.id)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="bk-add-more" @click="bookAnotherMaster">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Yana master qo'shish
                </button>

                <!-- Address Selection -->
                <div class="bk-address-section">
                    <h3 class="bk-address-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Manzil
                    </h3>

                    <!-- Saved Addresses -->
                    <div v-if="savedAddresses.length > 0 && !showManualAddress" class="bk-saved-addresses">
                        <div 
                            v-for="addr in savedAddresses" 
                            :key="addr.id"
                            class="bk-address-card"
                            :class="{ selected: selectedAddressId === addr.id }"
                            @click="selectedAddressId = addr.id"
                        >
                            <div class="bk-address-radio">
                                <div class="bk-radio-dot" v-if="selectedAddressId === addr.id"></div>
                            </div>
                            <div class="bk-address-info">
                                <span class="bk-address-name">{{ addr.name }}</span>
                                <span class="bk-address-text">{{ addr.full_address }}</span>
                            </div>
                            <span v-if="addr.is_default" class="bk-address-default">Asosiy</span>
                        </div>

                        <button class="bk-manual-address-btn" @click="showManualAddress = true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Boshqa manzil kiritish
                        </button>
                    </div>

                    <!-- Manual Address Input -->
                    <div v-if="savedAddresses.length === 0 || showManualAddress" class="bk-manual-address">
                        <div v-if="savedAddresses.length > 0" class="bk-back-to-saved">
                            <button @click="showManualAddress = false">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 18l-6-6 6-6"/>
                                </svg>
                                Saqlangan manzillar
                            </button>
                        </div>

                        <div class="bk-address-form">
                            <div class="bk-form-group">
                                <input 
                                    v-model="manualAddress.address" 
                                    type="text" 
                                    class="bk-input"
                                    placeholder="Manzil (ko'cha, uy raqami) *"
                                />
                            </div>
                            <div class="bk-form-row">
                                <input 
                                    v-model="manualAddress.entrance" 
                                    type="text" 
                                    class="bk-input bk-input-small"
                                    placeholder="Kirish"
                                />
                                <input 
                                    v-model="manualAddress.floor" 
                                    type="text" 
                                    class="bk-input bk-input-small"
                                    placeholder="Qavat"
                                />
                                <input 
                                    v-model="manualAddress.apartment" 
                                    type="text" 
                                    class="bk-input bk-input-small"
                                    placeholder="Xonadon"
                                />
                            </div>
                            <div class="bk-form-group">
                                <input 
                                    v-model="manualAddress.landmark" 
                                    type="text" 
                                    class="bk-input"
                                    placeholder="Mo'ljal (ixtiyoriy)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bk-cart-summary">
                    <div class="bk-cart-total">
                        <span>Jami</span>
                        <span>{{ formatPrice(cartTotal) }} so'm</span>
                    </div>
                </div>

                <p v-if="submitError" class="bk-error">{{ submitError }}</p>
            </div>
        </div>

        <!-- Payment View -->
        <div v-if="step === 'payment'" class="bk-content">
            <section class="bk-section">
                <div class="bk-payment-success">
                    <div class="bk-payment-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="16,8 10,14 8,12"/>
                        </svg>
                    </div>
                    <h2>Buyurtma qabul qilindi!</h2>
                    <p>Endi to'lovni amalga oshiring</p>
                </div>
            </section>

            <section class="bk-section">
                <h2 class="bk-section-title">To'lov usulini tanlang</h2>
                <div class="bk-payment-options">
                    <button v-if="payment?.payme_enabled" class="bk-payment-btn bk-payme" @click="payWithPayme">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        <span>Payme orqali to'lash</span>
                        <span class="bk-payment-amount">{{ formatPrice(cartTotal) }} so'm</span>
                    </button>
                    <button v-if="payment?.click_enabled" class="bk-payment-btn bk-click" @click="payWithClick">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        <span>Click orqali to'lash</span>
                        <span class="bk-payment-amount">{{ formatPrice(cartTotal) }} so'm</span>
                    </button>
                </div>
            </section>

            <button class="bk-pay-later" @click="payLater">
                Keyinroq to'layman
            </button>
        </div>

        <!-- Footer Actions -->
        <footer class="bk-footer">
            <div v-if="step === 1" class="bk-footer-content">
                <div class="bk-footer-price" v-if="selectedService">
                    <span class="bk-price-label">Narxi</span>
                    <span class="bk-price-value">{{ formatPrice(currentItemPrice) }} so'm</span>
                </div>
                <button class="bk-btn-primary" :disabled="!canProceedStep1" @click="nextStep">
                    Davom etish
                </button>
            </div>

            <div v-else-if="step === 2" class="bk-footer-content">
                <div class="bk-footer-price" v-if="selectedService">
                    <span class="bk-price-label">Narxi</span>
                    <span class="bk-price-value">{{ formatPrice(currentItemPrice) }} so'm</span>
                </div>
                <button class="bk-btn-primary" :disabled="!canProceedStep2" @click="nextStep">
                    Davom etish
                </button>
            </div>

            <div v-else-if="step === 3" class="bk-footer-content">
                <div class="bk-footer-price">
                    <span class="bk-price-label">Jami</span>
                    <span class="bk-price-value">{{ formatPrice(currentItemPrice) }} so'm</span>
                </div>
                <button class="bk-btn-primary" @click="addToCart">
                    Savatga qo'shish
                </button>
            </div>

            <div v-else-if="step === 'cart' && cart.length > 0" class="bk-footer-content">
                <div class="bk-footer-price">
                    <span class="bk-price-label">Jami</span>
                    <span class="bk-price-value">{{ formatPrice(cartTotal) }} so'm</span>
                </div>
                <button class="bk-btn-primary" :disabled="submitting || !hasValidAddress" @click="submitCart">
                    <span v-if="submitting">Yuborilmoqda...</span>
                    <span v-else>Buyurtma berish</span>
                </button>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Manrope:wght@400;500;600;700&display=swap');

.bk-page {
    --navy: #1B2B5A;
    --gold: #C8A951;
    --cream: #F5F2ED;
    --cream-dark: #E8E5DF;
    --text-muted: #8B8680;

    min-height: 100vh;
    background: linear-gradient(160deg, #F0EBE2 0%, #E5E0D7 50%, #DDD8CF 100%);
    font-family: 'Manrope', -apple-system, sans-serif;
    padding-bottom: 100px;
}

/* Header */
.bk-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.5);
    position: sticky;
    top: 0;
    z-index: 50;
}

.bk-back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 12px;
    color: var(--navy);
    cursor: pointer;
}

.bk-stepper {
    display: flex;
    align-items: center;
    gap: 4px;
}

.bk-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.bk-step-circle {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    transition: all 0.3s;
}

.bk-step.active .bk-step-circle,
.bk-step.done .bk-step-circle {
    background: var(--gold);
    border-color: var(--gold);
    color: white;
}

.bk-step-label {
    font-size: 9px;
    font-weight: 500;
    color: var(--text-muted);
}

.bk-step.active .bk-step-label {
    color: var(--navy);
}

.bk-step-line {
    width: 20px;
    height: 2px;
    background: rgba(255,255,255,0.5);
    margin: 0 4px;
    margin-bottom: 14px;
}

.bk-step-line.active {
    background: var(--gold);
}

.bk-header-title {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
}

.bk-cart-btn {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gold);
    border: none;
    border-radius: 12px;
    color: white;
    cursor: pointer;
}

.bk-cart-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--navy);
    border-radius: 50%;
    font-size: 10px;
    font-weight: 700;
    color: white;
}

.bk-header-space {
    width: 40px;
}

/* Content */
.bk-content {
    padding: 16px;
}

.bk-section {
    margin-bottom: 24px;
}

.bk-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 12px;
}

/* Service Cards */
.bk-service-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.bk-service-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px 12px;
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-service-card:hover {
    background: rgba(255,255,255,0.6);
}

.bk-service-card.selected {
    background: rgba(200,169,81,0.15);
    border-color: var(--gold);
}

.bk-service-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.5);
    border-radius: 12px;
    color: var(--gold);
}

.bk-service-card.selected .bk-service-icon {
    background: var(--gold);
    color: white;
}

.bk-service-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    text-align: center;
}

.bk-service-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gold);
    border-radius: 50%;
    color: white;
}

/* Chips */
.bk-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.bk-chip {
    padding: 10px 16px;
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: var(--navy);
    cursor: pointer;
    transition: all 0.2s;
}

.bk-chip:hover {
    background: rgba(255,255,255,0.6);
}

.bk-chip.selected {
    background: var(--gold);
    border-color: var(--gold);
    color: white;
}

/* Pressure Grid */
.bk-pressure-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.bk-pressure-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 8px;
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-pressure-card:hover {
    background: rgba(255,255,255,0.6);
}

.bk-pressure-card.selected {
    background: var(--gold);
    border-color: var(--gold);
    color: white;
}

.bk-pressure-icon {
    color: var(--gold);
}

.bk-pressure-card.selected .bk-pressure-icon {
    color: white;
}

.bk-pressure-card span {
    font-size: 12px;
    font-weight: 500;
}

/* Search Box */
.bk-search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 12px;
    margin-bottom: 12px;
}

.bk-search-box svg {
    color: var(--text-muted);
}

.bk-search-box input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 14px;
    color: var(--navy);
    outline: none;
}

.bk-search-box input::placeholder {
    color: var(--text-muted);
}

/* Master List */
.bk-master-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bk-master-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-master-card:hover {
    background: rgba(255,255,255,0.6);
}

.bk-master-card.selected {
    background: rgba(200,169,81,0.15);
    border-color: var(--gold);
}

.bk-master-photo {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    overflow: hidden;
    background: var(--gold);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.bk-master-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bk-master-info {
    flex: 1;
}

.bk-master-name {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--navy);
}

.bk-master-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
}

.bk-master-rating span {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
}

.bk-master-check {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gold);
    border-radius: 50%;
    color: white;
}

/* Date Row */
.bk-date-row {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}

.bk-date-card {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 10px 14px;
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-date-card:hover {
    background: rgba(255,255,255,0.6);
}

.bk-date-card.selected {
    background: var(--gold);
    border-color: var(--gold);
}

.bk-date-day {
    font-size: 10px;
    font-weight: 500;
    color: var(--text-muted);
    text-transform: uppercase;
}

.bk-date-card.selected .bk-date-day {
    color: rgba(255,255,255,0.8);
}

.bk-date-num {
    font-size: 18px;
    font-weight: 700;
    color: var(--navy);
}

.bk-date-card.selected .bk-date-num {
    color: white;
}

.bk-date-month {
    font-size: 10px;
    font-weight: 500;
    color: var(--text-muted);
}

.bk-date-card.selected .bk-date-month {
    color: rgba(255,255,255,0.8);
}

/* Slots Grouped */
.bk-slots-grouped {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.bk-slot-group {
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 14px;
    padding: 12px;
}

.bk-slot-group-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,255,255,0.5);
    color: var(--navy);
    font-size: 13px;
    font-weight: 600;
}

.bk-slot-group-header svg {
    color: var(--gold);
}

.bk-slot-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}

.bk-slot-btn {
    padding: 10px 4px;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
    color: var(--navy);
    cursor: pointer;
    transition: all 0.2s;
}

.bk-slot-btn:hover:not(:disabled) {
    border-color: var(--gold);
}

.bk-slot-btn.selected {
    background: var(--gold);
    border-color: var(--gold);
    color: white;
}

.bk-slot-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    text-decoration: line-through;
}

/* Confirm Card */
.bk-confirm-card {
    background: rgba(255,255,255,0.5);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 16px;
    padding: 16px;
}

.bk-confirm-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.bk-confirm-label {
    font-size: 14px;
    color: var(--text-muted);
}

.bk-confirm-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--navy);
}

.bk-confirm-divider {
    height: 1px;
    background: rgba(255,255,255,0.6);
    margin: 8px 0;
}

.bk-confirm-total .bk-confirm-value {
    color: var(--gold);
    font-size: 16px;
}

/* Textarea */
.bk-textarea {
    width: 100%;
    padding: 12px;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    color: var(--navy);
    resize: none;
}

.bk-textarea:focus {
    outline: none;
    border-color: var(--gold);
}

/* Empty & Loading */
.bk-empty {
    padding: 24px;
    text-align: center;
    color: var(--text-muted);
    font-size: 14px;
}

.bk-loading {
    display: flex;
    justify-content: center;
    padding: 24px;
}

.bk-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid rgba(200,169,81,0.2);
    border-top-color: var(--gold);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Cart */
.bk-cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px 20px;
    text-align: center;
    color: var(--text-muted);
}

.bk-cart-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.bk-cart-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 14px;
}

.bk-cart-item-photo {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--gold);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.bk-cart-item-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bk-cart-item-info {
    flex: 1;
}

.bk-cart-item-service {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--navy);
}

.bk-cart-item-meta,
.bk-cart-item-time {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
}

.bk-cart-item-right {
    text-align: right;
}

.bk-cart-item-price {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--gold);
}

.bk-cart-remove {
    padding: 4px;
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
}

.bk-add-more {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px;
    margin-top: 12px;
    background: rgba(255,255,255,0.4);
    border: 1px dashed var(--gold);
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    color: var(--gold);
    cursor: pointer;
}

.bk-cart-summary {
    margin-top: 16px;
    padding: 16px;
    background: rgba(255,255,255,0.5);
    border-radius: 14px;
}

.bk-cart-total {
    display: flex;
    justify-content: space-between;
    font-size: 16px;
    font-weight: 600;
    color: var(--navy);
}

.bk-error {
    margin-top: 12px;
    padding: 12px;
    background: #FEF2F2;
    border-radius: 10px;
    font-size: 14px;
    color: #EF4444;
    text-align: center;
}

/* Address Section */
.bk-address-section {
    margin-top: 20px;
    padding: 16px;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 16px;
}

.bk-address-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 14px;
}

.bk-address-title svg {
    color: var(--gold);
}

.bk-saved-addresses {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bk-address-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.6);
    border: 1px solid rgba(200,169,81,0.2);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-address-card:hover {
    border-color: var(--gold);
}

.bk-address-card.selected {
    background: rgba(200,169,81,0.1);
    border-color: var(--gold);
}

.bk-address-radio {
    width: 20px;
    height: 20px;
    border: 2px solid var(--gold);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bk-radio-dot {
    width: 10px;
    height: 10px;
    background: var(--gold);
    border-radius: 50%;
}

.bk-address-info {
    flex: 1;
    min-width: 0;
}

.bk-address-name {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--navy);
}

.bk-address-text {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.bk-address-default {
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
    background: var(--gold);
    color: white;
    border-radius: 10px;
    flex-shrink: 0;
}

.bk-manual-address-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    background: transparent;
    border: 1px dashed var(--gold);
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: var(--gold);
    cursor: pointer;
}

.bk-back-to-saved {
    margin-bottom: 12px;
}

.bk-back-to-saved button {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: rgba(255,255,255,0.5);
    border: none;
    border-radius: 8px;
    font-size: 13px;
    color: var(--navy);
    cursor: pointer;
}

.bk-address-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bk-form-group {
    width: 100%;
}

.bk-form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.bk-input {
    width: 100%;
    padding: 12px 14px;
    background: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: var(--navy);
}

.bk-input:focus {
    outline: none;
    border-color: var(--gold);
}

.bk-input::placeholder {
    color: var(--text-muted);
}

.bk-input-small {
    padding: 10px 12px;
    font-size: 13px;
}

/* Payment */
.bk-payment-success {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 32px 16px;
    text-align: center;
}

.bk-payment-icon {
    color: #10B981;
}

.bk-payment-success h2 {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    color: var(--navy);
    margin: 0;
}

.bk-payment-success p {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
}

.bk-payment-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.bk-payment-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: white;
    border: 2px solid transparent;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-payment-btn span:first-of-type {
    flex: 1;
    text-align: left;
    font-size: 15px;
    font-weight: 600;
}

.bk-payment-amount {
    font-size: 14px;
    font-weight: 600;
}

.bk-payme {
    border-color: #00CDBE;
    color: #00CDBE;
}

.bk-payme:hover {
    background: #E6FAF8;
}

.bk-click {
    border-color: #0066FF;
    color: #0066FF;
}

.bk-click:hover {
    background: #E6F0FF;
}

.bk-pay-later {
    width: 100%;
    margin-top: 16px;
    padding: 14px;
    background: transparent;
    border: none;
    font-size: 14px;
    color: var(--text-muted);
    cursor: pointer;
    text-decoration: underline;
}

/* Footer */
.bk-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255,255,255,0.6);
    padding: 12px 16px;
    padding-bottom: calc(12px + env(safe-area-inset-bottom));
    z-index: 100;
}

.bk-footer-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.bk-footer-price {
    flex: 1;
}

.bk-price-label {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
}

.bk-price-value {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: var(--navy);
}

.bk-btn-primary {
    flex: 1;
    padding: 14px 24px;
    background: var(--gold);
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-btn-primary:hover:not(:disabled) {
    background: #B8993F;
}

.bk-btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
