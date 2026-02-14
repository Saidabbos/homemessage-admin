<script setup>
import { ref, computed, watch, reactive } from 'vue';
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

// Per-person service data
const peopleCount = ref(1);
const activePerson = ref(1); // which person tab is active (1-based)
const peopleServices = reactive({}); // { 1: [{ service_id, duration_id }], 2: [...] }

// Initialize first person
peopleServices[1] = [];

// Shared booking data
const booking = ref({
    master_id: null,
    date: null,
    slot: null,
    pressure_level: 'medium',
    notes: '',
});

// Current person's services (shortcut)
const currentServices = computed(() => peopleServices[activePerson.value] || []);

// Set people count
const setPeopleCount = (count) => {
    const oldCount = peopleCount.value;
    peopleCount.value = count;

    // Add new person entries
    for (let i = oldCount + 1; i <= count; i++) {
        if (!peopleServices[i]) {
            peopleServices[i] = [];
        }
    }

    // Remove excess person entries
    for (let i = count + 1; i <= oldCount; i++) {
        delete peopleServices[i];
    }

    // Ensure active person is valid
    if (activePerson.value > count) {
        activePerson.value = count;
    }
};

// Toggle service for current person
const toggleService = (serviceId) => {
    const services = peopleServices[activePerson.value];
    const index = services.findIndex(s => s.service_id === serviceId);
    if (index >= 0) {
        services.splice(index, 1);
    } else {
        const service = props.services?.find(s => s.id === serviceId);
        const defaultDuration = service?.durations?.find(d => d.is_default) || service?.durations?.[0];
        services.push({
            service_id: serviceId,
            duration_id: defaultDuration?.id || null,
        });
    }
};

const isServiceSelected = (serviceId) => {
    return currentServices.value.some(s => s.service_id === serviceId);
};

const getServiceById = (serviceId) => {
    return props.services?.find(s => s.id === serviceId);
};

const getSelectedDurationForPerson = (personIndex, serviceId) => {
    const services = peopleServices[personIndex] || [];
    const sel = services.find(s => s.service_id === serviceId);
    const service = getServiceById(serviceId);
    return service?.durations?.find(d => d.id === sel?.duration_id);
};

const getSelectedDuration = (serviceId) => {
    return getSelectedDurationForPerson(activePerson.value, serviceId);
};

const setDuration = (serviceId, durationId) => {
    const services = peopleServices[activePerson.value];
    const sel = services.find(s => s.service_id === serviceId);
    if (sel) {
        sel.duration_id = durationId;
    }
};

// All services across all people (flat array)
const allServices = computed(() => {
    const result = [];
    for (let i = 1; i <= peopleCount.value; i++) {
        const services = peopleServices[i] || [];
        for (const sel of services) {
            result.push({ ...sel, person: i });
        }
    }
    return result;
});

// Total price: sum of all people's service prices
const totalPrice = computed(() => {
    let sum = 0;
    for (let i = 1; i <= peopleCount.value; i++) {
        const services = peopleServices[i] || [];
        for (const sel of services) {
            const dur = getSelectedDurationForPerson(i, sel.service_id);
            sum += Number(dur?.price) || 0;
        }
    }
    return sum;
});

// Total duration: sum of each person's service durations + buffer between people
const totalDuration = computed(() => {
    let maxPersonDuration = 0;
    for (let i = 1; i <= peopleCount.value; i++) {
        const services = peopleServices[i] || [];
        let personTotal = 0;
        for (const sel of services) {
            const dur = getSelectedDurationForPerson(i, sel.service_id);
            personTotal += dur?.duration || 0;
        }
        if (personTotal > maxPersonDuration) {
            maxPersonDuration = personTotal;
        }
    }
    const buffer = 10;
    return maxPersonDuration > 0
        ? maxPersonDuration * peopleCount.value + buffer * (peopleCount.value - 1)
        : 0;
});

// All unique service IDs across all people (for master filtering)
const allServiceIds = computed(() => {
    const ids = new Set();
    for (let i = 1; i <= peopleCount.value; i++) {
        const services = peopleServices[i] || [];
        for (const sel of services) {
            ids.add(sel.service_id);
        }
    }
    return [...ids];
});

// Person has at least one service selected
const personHasServices = (personIndex) => {
    return (peopleServices[personIndex] || []).length > 0;
};

// Person service count
const personServiceCount = (personIndex) => {
    return (peopleServices[personIndex] || []).length;
};

const selectedMaster = computed(() =>
    props.masters?.find(m => m.id === booking.value.master_id)
);

// Master search & scroll
const masterSearch = ref('');
const mastersRef = ref(null);

const scrollMasters = (dir) => {
    if (mastersRef.value) {
        mastersRef.value.scrollBy({ left: dir * 220, behavior: 'smooth' });
    }
};

const getMasterServiceIds = (m) => {
    return m.service_types?.map(st => st.id) || m.service_type_ids || [];
};

const filteredMasters = computed(() => {
    let masters = props.masters || [];

    // Filter by selected service types
    if (allServiceIds.value.length > 0) {
        masters = masters.filter(m => {
            const stIds = getMasterServiceIds(m);
            return allServiceIds.value.every(sid => stIds.includes(sid));
        });
    }

    // Filter by search
    if (masterSearch.value.trim()) {
        const q = masterSearch.value.toLowerCase().trim();
        masters = masters.filter(m =>
            (m.name || m.full_name || '').toLowerCase().includes(q) ||
            (m.first_name || '').toLowerCase().includes(q)
        );
    }

    return masters;
});

const getMasterSpecialties = (master) => {
    const stIds = getMasterServiceIds(master);
    return stIds.map(id => {
        const service = getServiceById(id);
        return service ? getTranslated(service.name) : '';
    }).filter(Boolean).slice(0, 2).join(' & ');
};

// Reset master/date/slot when services change
watch(allServiceIds, () => {
    booking.value.master_id = null;
    booking.value.date = null;
    booking.value.slot = null;
}, { deep: true });

// Available dates (next 7 days)
const monthNames = ['yan', 'fev', 'mar', 'apr', 'may', 'iyn', 'iyl', 'avg', 'sen', 'okt', 'noy', 'dek'];
const dayNames = ['Yak', 'Dush', 'Sesh', 'Chor', 'Pay', 'Jum', 'Shan'];

const availableDates = computed(() => {
    const dates = [];
    const today = new Date();
    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        dates.push({
            value: date.toISOString().split('T')[0],
            label: formatDate(date),
            dayName: dayNames[date.getDay()],
            day: date.getDate(),
            month: monthNames[date.getMonth()],
            fullLabel: `${date.getDate()}-${monthNames[date.getMonth()]}, ${dayNames[date.getDay()]}`,
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
        const response = await fetch(`/api/masters/${booking.value.master_id}/slots?date=${booking.value.date}&duration=${duration}&people_count=${peopleCount.value}`);
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

// Format slot as range "HH:MM - HH:MM"
const formatSlotRange = (start) => {
    if (!start) return '';
    const [h, m] = start.split(':').map(Number);
    const endM = m + 30;
    const endH = h + Math.floor(endM / 60);
    return `${start} - ${String(endH).padStart(2, '0')}:${String(endM % 60).padStart(2, '0')}`;
};

// Get selected date label
const selectedDateLabel = computed(() => {
    const d = availableDates.value.find(d => d.value === booking.value.date);
    return d ? d.fullLabel : '';
});

// Step navigation - all people must have at least 1 service with duration
const canProceedStep1 = computed(() => {
    for (let i = 1; i <= peopleCount.value; i++) {
        const services = peopleServices[i] || [];
        if (services.length === 0) return false;
        if (!services.every(s => s.service_id && s.duration_id)) return false;
    }
    return true;
});

const canProceedStep2 = computed(() =>
    booking.value.master_id && booking.value.date && booking.value.slot
);

// Format slot window display
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

        // Build per-person services for API
        const perPersonServices = [];
        for (let i = 1; i <= peopleCount.value; i++) {
            const services = peopleServices[i] || [];
            perPersonServices.push({
                person: i,
                services: services.map(sel => ({
                    service_type_id: sel.service_id,
                    duration_id: sel.duration_id,
                })),
            });
        }

        // Flat services list for backward compatibility
        const flatServices = allServices.value.map(sel => ({
            service_type_id: sel.service_id,
            duration_id: sel.duration_id,
        }));

        const response = await fetch('/api/public/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                services: flatServices,
                per_person_services: perPersonServices,
                service_type_id: flatServices[0]?.service_type_id,
                duration_id: flatServices[0]?.duration_id,
                master_id: booking.value.master_id,
                date: booking.value.date,
                arrival_window_start: booking.value.slot,
                people_count: peopleCount.value,
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
    { value: 'light', label: 'Yengil', icon: 'feather' },
    { value: 'medium', label: "O'rtacha", icon: 'waves' },
    { value: 'strong', label: 'Kuchli', icon: 'zap' },
];

// Get translated name from translatable field
const getTranslated = (field) => {
    if (typeof field === 'string') return field;
    if (field && typeof field === 'object') {
        const locale = document.documentElement.lang || 'uz';
        return field[locale] || field.uz || field.ru || field.en || Object.values(field)[0] || '';
    }
    return '';
};

// Service icons mapping
const getServiceIcon = (service) => {
    const name = getTranslated(service.name).toLowerCase();
    if (name.includes('classic') || name.includes('klassik')) return 'classic';
    if (name.includes('sport')) return 'sport';
    if (name.includes('relax') || name.includes('rileks')) return 'relax';
    if (name.includes('aroma')) return 'aroma';
    return 'classic';
};
</script>

<template>
    <Head title="Buyurtma berish" />

    <div class="bk-page">
        <!-- Top Bar -->
        <div class="bk-topbar">
            <a href="/" class="bk-logo">HOMEMASSAGE</a>

            <!-- Stepper -->
            <div class="bk-stepper">
                <div class="bk-step" :class="{ active: step >= 1, completed: step > 1 }">
                    <div class="bk-step-circle">
                        <svg v-if="step > 1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                        <span v-else>1</span>
                    </div>
                    <span class="bk-step-label">Xizmat</span>
                </div>
                <div class="bk-step-line" :class="{ active: step > 1 }"></div>
                <div class="bk-step" :class="{ active: step >= 2, completed: step > 2 }">
                    <div class="bk-step-circle">
                        <svg v-if="step > 2" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                        <span v-else>2</span>
                    </div>
                    <span class="bk-step-label">Vaqt</span>
                </div>
                <div class="bk-step-line" :class="{ active: step > 2 }"></div>
                <div class="bk-step" :class="{ active: step >= 3 }">
                    <div class="bk-step-circle">
                        <span>3</span>
                    </div>
                    <span class="bk-step-label">Tasdiqlash</span>
                </div>
            </div>

            <!-- Close Button -->
            <a href="/" class="bk-close-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </a>
        </div>

        <!-- Two Column Layout -->
        <div class="bk-layout">
            <!-- Left Column: Form -->
            <div class="bk-form-col">
                <!-- Step 1: Service Selection -->
                <div v-if="step === 1" class="bk-step-content">
                    <!-- People Count — FIRST -->
                    <div class="bk-section">
                        <h3 class="bk-section-title">Necha kishi?</h3>
                        <div class="bk-people-grid">
                            <button
                                v-for="n in 4"
                                :key="n"
                                class="bk-people-btn"
                                :class="{ selected: peopleCount === n }"
                                @click="setPeopleCount(n)"
                            >
                                <div class="bk-people-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span v-if="n > 1" class="bk-people-badge">x{{ n }}</span>
                                </div>
                                <span class="bk-people-label">{{ n }} {{ n === 1 ? 'kishi' : 'kishi' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Person Tabs (if multiple people) -->
                    <div v-if="peopleCount > 1" class="bk-section">
                        <div class="bk-person-tabs">
                            <button
                                v-for="n in peopleCount"
                                :key="n"
                                class="bk-person-tab"
                                :class="{ active: activePerson === n, 'has-services': personHasServices(n) }"
                                @click="activePerson = n"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <span>{{ n }}-kishi</span>
                                <span v-if="personServiceCount(n) > 0" class="bk-tab-badge">{{ personServiceCount(n) }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Massage Type (per-person) -->
                    <div class="bk-section">
                        <h3 class="bk-section-title">
                            Massaj turi
                            <span v-if="peopleCount > 1" class="bk-section-hint">({{ activePerson }}-kishi uchun)</span>
                        </h3>
                        <div class="bk-service-grid">
                            <div
                                v-for="service in services"
                                :key="service.id"
                                class="bk-service-card"
                                :class="{ selected: isServiceSelected(service.id) }"
                                @click="toggleService(service.id)"
                            >
                                <div class="bk-service-icon">
                                    <svg v-if="getServiceIcon(service) === 'classic'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                                    </svg>
                                    <svg v-else-if="getServiceIcon(service) === 'sport'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>
                                    </svg>
                                    <svg v-else-if="getServiceIcon(service) === 'relax'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    </svg>
                                    <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
                                    </svg>
                                </div>
                                <span class="bk-service-name">{{ getTranslated(service.name) }}</span>
                                <div class="bk-service-check" v-if="isServiceSelected(service.id)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Duration Picker (per-person) -->
                    <div v-if="currentServices.length > 0" class="bk-section">
                        <h3 class="bk-section-title">Davomiylik</h3>
                        <div
                            v-for="sel in currentServices"
                            :key="sel.service_id"
                            class="bk-duration-group"
                        >
                            <p v-if="currentServices.length > 1" class="bk-duration-label">{{ getTranslated(getServiceById(sel.service_id)?.name) }}</p>
                            <div class="bk-duration-row">
                                <button
                                    v-for="dur in getServiceById(sel.service_id)?.durations"
                                    :key="dur.id"
                                    class="bk-duration-btn"
                                    :class="{ selected: sel.duration_id === dur.id }"
                                    @click="setDuration(sel.service_id, dur.id)"
                                >
                                    {{ dur.duration }} min
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pressure Level -->
                    <div class="bk-section">
                        <h3 class="bk-section-title">Bosim kuchi</h3>
                        <div class="bk-pressure-row">
                            <button
                                v-for="level in pressureLevels"
                                :key="level.value"
                                class="bk-pressure-btn"
                                :class="{ selected: booking.pressure_level === level.value }"
                                @click="booking.pressure_level = level.value"
                            >
                                <div class="bk-pressure-icon">
                                    <svg v-if="level.icon === 'feather'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.24 12.24a6 6 0 00-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/><line x1="17.5" y1="15" x2="9" y2="15"/>
                                    </svg>
                                    <svg v-else-if="level.icon === 'waves'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                        <path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                        <path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                    </svg>
                                    <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                    </svg>
                                </div>
                                <span>{{ level.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Master & Time Selection -->
                <div v-if="step === 2" class="bk-step-content">
                    <!-- Step Title -->
                    <div class="bk-step-header">
                        <h2 class="bk-step-heading">Master va vaqtni tanlang</h2>
                        <p class="bk-step-subheading">O'zingizga mos master, sana va vaqt oralig'ini belgilang</p>
                    </div>

                    <!-- Master Selection -->
                    <div class="bk-section">
                        <label class="bk-field-label">Masterni tanlang</label>
                        <div class="bk-search-input">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input v-model="masterSearch" type="text" placeholder="Ism bo'yicha qidirish..." />
                        </div>

                        <div v-if="filteredMasters.length === 0" class="bk-empty">
                            Bu xizmat uchun master topilmadi
                        </div>
                        <div v-else class="bk-masters-slider">
                            <button class="bk-slider-arrow bk-slider-arrow-left" @click="scrollMasters(-1)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15,18 9,12 15,6"/></svg>
                            </button>
                            <div ref="mastersRef" class="bk-masters-track">
                                <div
                                    v-for="master in filteredMasters"
                                    :key="master.id"
                                    class="bk-master-card"
                                    :class="{ selected: booking.master_id === master.id }"
                                    @click="booking.master_id = master.id"
                                >
                                    <div class="bk-master-img">
                                        <img :src="master.photo_url" :alt="master.name" />
                                    </div>
                                    <div class="bk-master-info">
                                        <span class="bk-master-name">{{ master.name }}</span>
                                        <span class="bk-master-spec">{{ getMasterSpecialties(master) || 'Massaj' }}</span>
                                        <div class="bk-master-rating">
                                            <svg v-for="s in 5" :key="s" width="10" height="10" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
                                            <span class="bk-master-rating-text">5.0</span>
                                        </div>
                                        <button class="bk-master-select-btn" :class="{ active: booking.master_id === master.id }">
                                            {{ booking.master_id === master.id ? 'Tanlangan' : 'Tanlash' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button class="bk-slider-arrow bk-slider-arrow-right" @click="scrollMasters(1)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9,18 15,12 9,6"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Date Selection -->
                    <div class="bk-section">
                        <label class="bk-field-label">Sanani tanlang</label>
                        <div class="bk-dates-row">
                            <button
                                v-for="d in availableDates"
                                :key="d.value"
                                class="bk-date-btn"
                                :class="{ selected: booking.date === d.value }"
                                @click="booking.date = d.value"
                            >
                                <span class="bk-date-day">{{ d.dayName }}</span>
                                <span class="bk-date-num">{{ d.day }}</span>
                                <span class="bk-date-month">{{ d.month }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Time Slots -->
                    <div v-if="booking.master_id && booking.date" class="bk-section">
                        <label class="bk-field-label">Vaqt oralig'ini tanlang</label>
                        <div v-if="loadingSlots" class="bk-loading">
                            <div class="bk-spinner"></div>
                            <span>Yuklanmoqda...</span>
                        </div>
                        <div v-else-if="availableSlots.length === 0" class="bk-empty">
                            Bu kunga bo'sh vaqt yo'q
                        </div>
                        <div v-else class="bk-slots-grid">
                            <button
                                v-for="slot in availableSlots"
                                :key="slot.start"
                                class="bk-slot-btn"
                                :class="{ selected: booking.slot === slot.start, disabled: slot.disabled }"
                                :disabled="slot.disabled"
                                @click="!slot.disabled && (booking.slot = slot.start)"
                            >
                                {{ formatSlotRange(slot.start) }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Confirmation -->
                <div v-if="step === 3" class="bk-step-content">
                    <div class="bk-confirm-card">
                        <h3 class="bk-confirm-title">Buyurtma ma'lumotlari</h3>

                        <template v-for="p in peopleCount" :key="'confirm-person-' + p">
                            <div v-if="peopleCount > 1" class="bk-confirm-person-header">{{ p }}-kishi</div>
                            <div v-for="sel in peopleServices[p]" :key="'p' + p + '-' + sel.service_id" class="bk-confirm-row">
                                <span class="bk-confirm-label">{{ getTranslated(getServiceById(sel.service_id)?.name) }}</span>
                                <span class="bk-confirm-value">{{ getSelectedDurationForPerson(p, sel.service_id)?.duration }} min</span>
                            </div>
                        </template>

                        <div class="bk-confirm-row">
                            <span class="bk-confirm-label">Kishi soni</span>
                            <span class="bk-confirm-value">{{ peopleCount }}</span>
                        </div>

                        <div class="bk-confirm-divider"></div>

                        <div class="bk-confirm-row">
                            <span class="bk-confirm-label">Master</span>
                            <span class="bk-confirm-value">{{ selectedMaster?.name }}</span>
                        </div>

                        <div class="bk-confirm-row">
                            <span class="bk-confirm-label">Sana</span>
                            <span class="bk-confirm-value">{{ booking.date }}</span>
                        </div>

                        <div class="bk-confirm-row">
                            <span class="bk-confirm-label">Kelish oynasi</span>
                            <span class="bk-confirm-value">{{ slotDisplay }}</span>
                        </div>

                        <div class="bk-confirm-row">
                            <span class="bk-confirm-label">Bosim</span>
                            <span class="bk-confirm-value">{{ pressureLevels.find(p => p.value === booking.pressure_level)?.label }}</span>
                        </div>
                    </div>

                    <div class="bk-section" style="margin-top: 24px;">
                        <h3 class="bk-section-title">Sizning ma'lumotlaringiz</h3>
                        <div class="bk-customer-card">
                            <div class="bk-customer-row">
                                <span class="bk-confirm-label">Ism</span>
                                <span class="bk-confirm-value">{{ customer?.name || '-' }}</span>
                            </div>
                            <div class="bk-customer-row">
                                <span class="bk-confirm-label">Telefon</span>
                                <span class="bk-confirm-value">{{ customer?.phone || '-' }}</span>
                            </div>
                        </div>

                        <div class="bk-notes-group">
                            <label class="bk-notes-label">Qo'shimcha izoh (ixtiyoriy)</label>
                            <textarea
                                v-model="booking.notes"
                                class="bk-notes-textarea"
                                placeholder="Masalan: 5-qavat, 25-xonadon"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <div v-if="submitError" class="bk-error">
                        {{ submitError }}
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="bk-summary-col">
                <div class="bk-summary-card">
                    <h3 class="bk-summary-title">{{ step >= 2 ? 'Buyurtma xulosasi' : 'Buyurtma' }}</h3>

                    <div v-if="allServices.length > 0" class="bk-summary-items">
                        <div class="bk-summary-divider" style="margin-top: 0;"></div>

                        <!-- Service info -->
                        <template v-for="p in peopleCount" :key="'summary-p-' + p">
                            <div v-if="peopleCount > 1 && (peopleServices[p] || []).length > 0" class="bk-summary-person">{{ p }}-kishi</div>
                            <div v-for="sel in (peopleServices[p] || [])" :key="'sp' + p + '-' + sel.service_id" class="bk-summary-row">
                                <span class="bk-summary-label">{{ getTranslated(getServiceById(sel.service_id)?.name) }}</span>
                                <span class="bk-summary-value">{{ getSelectedDurationForPerson(p, sel.service_id)?.duration || '—' }} min</span>
                            </div>
                        </template>

                        <div class="bk-summary-row">
                            <span class="bk-summary-label">Kishi soni</span>
                            <span class="bk-summary-value">{{ peopleCount }}</span>
                        </div>
                        <div class="bk-summary-row">
                            <span class="bk-summary-label">Bosim</span>
                            <span class="bk-summary-value">{{ pressureLevels.find(p => p.value === booking.pressure_level)?.label }}</span>
                        </div>

                        <!-- Step 2+ info: date, master, time -->
                        <template v-if="step >= 2">
                            <div v-if="selectedDateLabel" class="bk-summary-row">
                                <span class="bk-summary-label">Sana</span>
                                <span class="bk-summary-value">{{ selectedDateLabel }}</span>
                            </div>
                            <div v-if="selectedMaster" class="bk-summary-row">
                                <span class="bk-summary-label">Master</span>
                                <span class="bk-summary-value">{{ selectedMaster.name }}</span>
                            </div>
                        </template>
                    </div>
                    <div v-else class="bk-summary-empty">
                        Xizmat tanlang
                    </div>

                    <div class="bk-summary-divider"></div>

                    <div class="bk-summary-total">
                        <span class="bk-summary-total-label">Jami</span>
                        <span class="bk-summary-total-value">{{ formatPrice(totalPrice) }} so'm</span>
                    </div>

                    <!-- Show time after total (like Pencil design) -->
                    <div v-if="step >= 2 && booking.slot" class="bk-summary-row" style="margin-top: 12px;">
                        <span class="bk-summary-label">Vaqt</span>
                        <span class="bk-summary-value">{{ formatSlotRange(booking.slot) }}</span>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="bk-actions">
                    <button
                        v-if="step > 1"
                        class="bk-btn-back"
                        @click="prevStep"
                    >
                        Orqaga
                    </button>

                    <button
                        v-if="step === 1"
                        class="bk-btn-next"
                        :disabled="!canProceedStep1"
                        @click="nextStep"
                    >
                        Davom etish
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12,5 19,12 12,19"/></svg>
                    </button>
                    <button
                        v-else-if="step === 2"
                        class="bk-btn-next"
                        :disabled="!canProceedStep2"
                        @click="nextStep"
                    >
                        Keyingi qadam
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12,5 19,12 12,19"/></svg>
                    </button>
                    <button
                        v-else
                        class="bk-btn-next bk-btn-submit"
                        :disabled="!canSubmit || submitting"
                        @click="submitBooking"
                    >
                        <span v-if="submitting">Yuborilmoqda...</span>
                        <span v-else>Buyurtma berish</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Styles loaded from resources/css/public/booking.css via app.css -->
