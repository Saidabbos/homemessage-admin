<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useCart } from '@/composables/useCart';

const { t } = useI18n();
const { cart, cartTotal, cartItemCount, clearCart } = useCart();

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const props = defineProps({
    services: Array,
    masters: Array,
    customer: Object,
});

// Wizard state: 1 | 2 | 3 | 'cart'
const step = ref(1);

// Single service selection (replaces per-person system)
const selectedService = ref(null); // { service_id, duration_id }

// Address state
const savedAddresses = ref([]);
const selectedAddressId = ref(null);
const showManualAddress = ref(false);
const manualAddress = ref({
    address: '',
    entrance: '',
    floor: '',
    apartment: '',
    landmark: '',
    lat: null,
    lng: null,
});

// Map state
const showMap = ref(false);
const mapContainerRef = ref(null);
const detectingLocation = ref(false);
const mapCenter = ref([41.2995, 69.2401]);
const mapMarker = ref(null);
let leafletMap = null;
let leafletMarker = null;

// Shared booking data
const booking = ref({
    master_id: null,
    date: null,
    slot: null,
    pressure_level: null,
    notes: '',
});

// ==================== Service Selection ====================

const toggleService = (serviceId) => {
    if (selectedService.value?.service_id === serviceId) {
        selectedService.value = null;
    } else {
        selectedService.value = {
            service_id: serviceId,
            duration_id: null,
        };
    }
};

const isServiceSelected = (serviceId) => {
    return selectedService.value?.service_id === serviceId;
};

const getServiceById = (serviceId) => {
    return props.services?.find(s => s.id === serviceId);
};

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

// cartTotal and cartItemCount come from useCart composable

// Service IDs for master filtering
const selectedServiceIds = computed(() => {
    return selectedService.value ? [selectedService.value.service_id] : [];
});

// ==================== Master ====================

const selectedMaster = computed(() =>
    props.masters?.find(m => m.id === booking.value.master_id)
);

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

    if (selectedServiceIds.value.length > 0) {
        masters = masters.filter(m => {
            const stIds = getMasterServiceIds(m);
            return selectedServiceIds.value.every(sid => stIds.includes(sid));
        });
    }

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

// Reset master/date/slot when service changes
watch(() => selectedService.value?.service_id, () => {
    booking.value.master_id = null;
    booking.value.date = null;
    booking.value.slot = null;
});

// ==================== Dates & Slots ====================

const monthNames = ['yan', 'fev', 'mar', 'apr', 'may', 'iyn', 'iyl', 'avg', 'sen', 'okt', 'noy', 'dek'];
const dayNames = ['Yak', 'Dush', 'Sesh', 'Chor', 'Pay', 'Jum', 'Shan'];

const availableDates = computed(() => {
    const dates = [];
    const today = new Date();
    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        dates.push({
            value: `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`,
            label: formatDate(date),
            dayName: dayNames[date.getDay()],
            day: date.getDate(),
            month: monthNames[date.getMonth()],
            fullLabel: `${date.getDate()}-${monthNames[date.getMonth()]}, ${dayNames[date.getDay()]}`,
        });
    }
    return dates;
});

const availableSlots = ref([]);
const loadingSlots = ref(false);

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

// Get slots that are already in cart for current master/date
const cartBlockedSlots = computed(() => {
    if (!booking.value.master_id || !booking.value.date) return new Set();
    
    const blocked = new Set();
    for (const item of cart.value) {
        if (item.master_id === booking.value.master_id && item.date === booking.value.date) {
            blocked.add(item.slot);
        }
    }
    return blocked;
});

// Process slots with cart blocking
const processedSlots = computed(() => {
    const blocked = cartBlockedSlots.value;
    return availableSlots.value.map(slot => ({
        ...slot,
        disabled: slot.disabled || blocked.has(slot.start),
        inCart: blocked.has(slot.start),
    }));
});

// ==================== Formatting ====================

const formatDate = (date) => {
    return date.toLocaleDateString('uz-UZ', { month: 'short', day: 'numeric' });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price);
};

const formatSlotRange = (start) => {
    if (!start) return '';
    const [h, m] = start.split(':').map(Number);
    const endM = m + 30;
    const endH = h + Math.floor(endM / 60);
    return `${start} - ${String(endH).padStart(2, '0')}:${String(endM % 60).padStart(2, '0')}`;
};

const selectedDateLabel = computed(() => {
    const d = availableDates.value.find(d => d.value === booking.value.date);
    return d ? d.fullLabel : '';
});

const slotDisplay = computed(() => {
    if (!booking.value.slot) return '';
    const [hours, minutes] = booking.value.slot.split(':').map(Number);
    const endMinutes = minutes + 30;
    const endHours = hours + Math.floor(endMinutes / 60);
    const endMins = endMinutes % 60;
    return `${booking.value.slot}–${String(endHours).padStart(2, '0')}:${String(endMins).padStart(2, '0')}`;
});

// ==================== Navigation ====================

const canProceedStep1 = computed(() => {
    return selectedService.value?.service_id && 
           selectedService.value?.duration_id && 
           booking.value.pressure_level;
});

const canProceedStep2 = computed(() =>
    booking.value.master_id && booking.value.date && booking.value.slot
);

const canSubmit = computed(() => canProceedStep2.value);

const nextStep = () => {
    if (step.value < 3) step.value++;
};

const prevStep = () => {
    if (step.value === 'cart') {
        step.value = 3;
    } else if (step.value > 1) {
        step.value--;
    }
};

// ==================== Cart ====================

const addToCart = () => {
    if (!canSubmit.value) return;

    const service = getServiceById(selectedService.value.service_id);
    const duration = getSelectedDuration(selectedService.value.service_id);
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
        service_name: getTranslated(service.name),
        duration_minutes: duration.duration,
        price: Number(duration.price),
        master_name: selectedMaster.value.name,
        master_photo: selectedMaster.value.photo_url,
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
        pressure_level: null,
        notes: '',
    };
    step.value = 1;
};

// ==================== Submit Cart ====================

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

        // Address data (optional)
        const addr = addressForSubmit.value;
        const payload = { orders };
        if (addr?.address) {
            payload.address = addr.address;
            payload.entrance = addr.entrance;
            payload.floor = addr.floor;
            payload.apartment = addr.apartment;
            payload.landmark = addr.landmark;
            payload.customer_address_id = addr.customer_address_id;
        }

        const response = await fetch('/api/public/orders/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (result.success) {
            clearCart();
            router.visit('/booking/payment/' + result.group_id);
        } else {
            submitError.value = result.message || 'Xatolik yuz berdi';
        }
    } catch (e) {
        console.error('Cart submission failed:', e);
        submitError.value = 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
    }
    submitting.value = false;
};

// ==================== Constants & Helpers ====================

const pressureLevels = [
    { value: 'soft', label: 'Yengil', icon: 'feather' },
    { value: 'medium', label: "O'rtacha", icon: 'waves' },
    { value: 'hard', label: 'Kuchli', icon: 'zap' },
];

// ==================== Address ====================

const loadSavedAddresses = async () => {
    if (!authUser.value) return;
    try {
        const response = await fetch('/api/user/addresses');
        const data = await response.json();
        savedAddresses.value = data.addresses || [];
        // Auto-select default address
        const defaultAddr = savedAddresses.value.find(a => a.is_default);
        if (defaultAddr) {
            selectedAddressId.value = defaultAddr.id;
        }
    } catch (e) {
        console.error('Failed to load addresses:', e);
    }
};

// Load addresses on mount
loadSavedAddresses();

const selectedAddress = computed(() => {
    if (!selectedAddressId.value) return null;
    return savedAddresses.value.find(a => a.id === selectedAddressId.value);
});

const addressForSubmit = computed(() => {
    if (showManualAddress.value || savedAddresses.value.length === 0) {
        // Manual address
        return {
            address: manualAddress.value.address,
            entrance: manualAddress.value.entrance,
            floor: manualAddress.value.floor,
            apartment: manualAddress.value.apartment,
            landmark: manualAddress.value.landmark,
            customer_address_id: null,
        };
    } else if (selectedAddress.value) {
        // Saved address
        return {
            address: selectedAddress.value.address,
            entrance: selectedAddress.value.entrance,
            floor: selectedAddress.value.floor,
            apartment: selectedAddress.value.apartment,
            landmark: selectedAddress.value.landmark,
            customer_address_id: selectedAddress.value.id,
        };
    }
    return null;
});

// ==================== Map Functions ====================

const initLeafletMap = async () => {
    await nextTick();
    if (!mapContainerRef.value || leafletMap) return;
    
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });
    
    const center = mapMarker.value || mapCenter.value;
    leafletMap = L.map(mapContainerRef.value).setView(center, 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(leafletMap);
    
    if (mapMarker.value) {
        leafletMarker = L.marker(mapMarker.value, { draggable: true }).addTo(leafletMap);
        leafletMarker.on('dragend', async (e) => {
            const latlng = e.target.getLatLng();
            mapMarker.value = [latlng.lat, latlng.lng];
            manualAddress.value.lat = latlng.lat;
            manualAddress.value.lng = latlng.lng;
            await reverseGeocode(latlng.lat, latlng.lng);
        });
    }
    
    leafletMap.on('click', async (e) => {
        const { lat, lng } = e.latlng;
        mapMarker.value = [lat, lng];
        manualAddress.value.lat = lat;
        manualAddress.value.lng = lng;
        
        if (leafletMarker) {
            leafletMarker.setLatLng([lat, lng]);
        } else {
            const L = await import('leaflet');
            leafletMarker = L.marker([lat, lng], { draggable: true }).addTo(leafletMap);
            leafletMarker.on('dragend', async (e) => {
                const latlng = e.target.getLatLng();
                mapMarker.value = [latlng.lat, latlng.lng];
                manualAddress.value.lat = latlng.lat;
                manualAddress.value.lng = latlng.lng;
                await reverseGeocode(latlng.lat, latlng.lng);
            });
        }
        
        await reverseGeocode(lat, lng);
    });
};

const cleanupMap = () => {
    if (leafletMap) {
        leafletMap.remove();
        leafletMap = null;
        leafletMarker = null;
    }
};

watch(showMap, async (visible) => {
    if (visible) {
        await nextTick();
        initLeafletMap();
    } else {
        cleanupMap();
    }
});

const reverseGeocode = async (lat, lng) => {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=uz`
        );
        const data = await response.json();
        
        if (data.display_name) {
            const addr = data.address || {};
            const parts = [];
            if (addr.road) parts.push(addr.road);
            if (addr.house_number) parts.push(addr.house_number);
            if (addr.neighbourhood) parts.push(addr.neighbourhood);
            if (addr.suburb) parts.push(addr.suburb);
            
            manualAddress.value.address = parts.length > 0 ? parts.join(', ') : data.display_name.split(',').slice(0, 3).join(',');
        }
    } catch (e) {
        console.error('Geocode error:', e);
    }
};

const detectLocation = async () => {
    if (!navigator.geolocation) {
        alert('Geolokatsiya qo\'llab-quvvatlanmaydi');
        return;
    }
    
    detectingLocation.value = true;
    
    try {
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        });
        
        const { latitude, longitude } = position.coords;
        mapCenter.value = [latitude, longitude];
        mapMarker.value = [latitude, longitude];
        manualAddress.value.lat = latitude;
        manualAddress.value.lng = longitude;
        
        if (leafletMap) {
            const L = await import('leaflet');
            leafletMap.setView([latitude, longitude], 16);
            
            if (leafletMarker) {
                leafletMarker.setLatLng([latitude, longitude]);
            } else {
                leafletMarker = L.marker([latitude, longitude], { draggable: true }).addTo(leafletMap);
                leafletMarker.on('dragend', async (e) => {
                    const latlng = e.target.getLatLng();
                    mapMarker.value = [latlng.lat, latlng.lng];
                    manualAddress.value.lat = latlng.lat;
                    manualAddress.value.lng = latlng.lng;
                    await reverseGeocode(latlng.lat, latlng.lng);
                });
            }
        }
        
        await reverseGeocode(latitude, longitude);
        
        if (!showMap.value) {
            showMap.value = true;
        }
    } catch (error) {
        console.error('Location error:', error);
        alert('Joylashuvni aniqlab bo\'lmadi');
    } finally {
        detectingLocation.value = false;
    }
};

const confirmMapLocation = () => {
    showMap.value = false;
};

const getTranslated = (field) => {
    if (typeof field === 'string') return field;
    if (field && typeof field === 'object') {
        const locale = document.documentElement.lang || 'uz';
        return field[locale] || field.uz || field.ru || field.en || Object.values(field)[0] || '';
    }
    return '';
};

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
        <!-- Liquid Glass Background Blobs -->
        <div class="bg-blob-1" style="top: 5%; right: -150px; opacity: 0.4;"></div>
        <div class="bg-blob-2" style="bottom: 20%; left: -100px; opacity: 0.3;"></div>
        <!-- Top Bar -->
        <div class="bk-topbar">
            <a href="/" class="bk-logo">HOMEMASSAGE</a>

            <!-- Stepper (steps 1-3) -->
            <div v-if="step !== 'cart'" class="bk-stepper">
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

            <!-- Cart header (cart view) -->
            <div v-else class="bk-cart-header-bar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
                <span class="bk-cart-header-label">Savat</span>
                <span v-if="cartItemCount > 0" class="bk-cart-header-count">{{ cartItemCount }}</span>
            </div>

            <!-- Close / Cart toggle / User -->
            <div class="bk-topbar-right">
                <!-- User avatar -->
                <a v-if="authUser" href="/customer/dashboard" class="bk-user-avatar" :title="authUser.name">
                    {{ authUser.avatar }}
                </a>

                <button
                    v-if="step !== 'cart' && cartItemCount > 0"
                    class="bk-cart-toggle"
                    @click="step = 'cart'"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                    <span class="bk-cart-toggle-count">{{ cartItemCount }}</span>
                </button>
                <a href="/" class="bk-close-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="bk-layout">
            <!-- Left Column: Form -->
            <div class="bk-form-col">
                <!-- Step 1: Service Selection -->
                <div v-if="step === 1" class="bk-step-content">
                    <!-- Massage Type -->
                    <div class="bk-section">
                        <h3 class="bk-section-title">Massaj turi</h3>
                        <div class="bk-service-grid">
                            <div
                                v-for="(service, idx) in services"
                                :key="service.id"
                                class="bk-service-card glass-card"
                                :class="{ selected: isServiceSelected(service.id) }"
                                :style="{ animationDelay: (idx * 0.05) + 's' }"
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

                    <!-- Duration Picker -->
                    <div v-if="selectedService" class="bk-section">
                        <h3 class="bk-section-title">Davomiylik</h3>
                        <div class="bk-duration-row">
                            <button
                                v-for="dur in getServiceById(selectedService.service_id)?.durations"
                                :key="dur.id"
                                class="bk-duration-btn"
                                :class="{ selected: selectedService.duration_id === dur.id }"
                                @click="setDuration(selectedService.service_id, dur.id)"
                            >
                                {{ dur.duration }} min
                            </button>
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
                                    v-for="(master, idx) in filteredMasters"
                                    :key="master.id"
                                    class="bk-master-card glass-card"
                                    :class="{ selected: booking.master_id === master.id }"
                                    :style="{ animationDelay: (idx * 0.1) + 's' }"
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
                        <label class="bk-field-label">{{ t('booking.master_arrival_time') }}</label>
                        <div v-if="loadingSlots" class="bk-loading">
                            <div class="bk-spinner"></div>
                            <span>Yuklanmoqda...</span>
                        </div>
                        <div v-else-if="processedSlots.length === 0" class="bk-empty">
                            Bu kunga bo'sh vaqt yo'q
                        </div>
                        <div v-else class="bk-slots-grid">
                            <button
                                v-for="slot in processedSlots"
                                :key="slot.start"
                                class="bk-slot-btn"
                                :class="{ selected: booking.slot === slot.start, disabled: slot.disabled, 'in-cart': slot.inCart }"
                                :disabled="slot.disabled"
                                @click="!slot.disabled && (booking.slot = slot.start)"
                                :title="slot.inCart ? 'Savatda mavjud' : ''"
                            >
                                {{ formatSlotRange(slot.start) }}
                                <span v-if="slot.inCart" class="slot-cart-badge">🛒</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Confirmation -->
                <div v-if="step === 3" class="bk-step-content">
                    <div class="bk-confirm-card">
                        <h3 class="bk-confirm-title">Buyurtma ma'lumotlari</h3>

                        <div class="bk-confirm-row">
                            <span class="bk-confirm-label">{{ getTranslated(getServiceById(selectedService.service_id)?.name) }}</span>
                            <span class="bk-confirm-value">{{ getSelectedDuration(selectedService.service_id)?.duration }} min</span>
                        </div>

                        <div class="bk-confirm-divider"></div>

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
                    </div>

                    <!-- Address Section (Optional) -->
                    <div class="bk-section" style="margin-top: 24px;">
                        <h3 class="bk-section-title">Manzil (ixtiyoriy)</h3>
                        
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
                                    <div class="bk-radio" :class="{ checked: selectedAddressId === addr.id }"></div>
                                </div>
                                <div class="bk-address-info">
                                    <span class="bk-address-name">{{ addr.name }}</span>
                                    <span class="bk-address-text">{{ addr.full_address }}</span>
                                </div>
                                <span v-if="addr.is_default" class="bk-address-badge">Asosiy</span>
                            </div>
                            <button class="bk-manual-btn" @click="showManualAddress = true; selectedAddressId = null">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Boshqa manzil kiritish
                            </button>
                        </div>

                        <!-- Manual Address Input -->
                        <div v-if="showManualAddress || savedAddresses.length === 0" class="bk-manual-address">
                            <button 
                                v-if="savedAddresses.length > 0" 
                                class="bk-back-to-saved" 
                                @click="showManualAddress = false"
                            >
                                ← Saqlangan manzillar
                            </button>
                            <div class="bk-address-field">
                                <input
                                    v-model="manualAddress.address"
                                    type="text"
                                    class="bk-input"
                                    placeholder="Manzil (ko'cha, uy)"
                                />
                            </div>
                            <div class="bk-address-row">
                                <input
                                    v-model="manualAddress.entrance"
                                    type="text"
                                    class="bk-input bk-input-sm"
                                    placeholder="Kirish"
                                />
                                <input
                                    v-model="manualAddress.floor"
                                    type="text"
                                    class="bk-input bk-input-sm"
                                    placeholder="Qavat"
                                />
                                <input
                                    v-model="manualAddress.apartment"
                                    type="text"
                                    class="bk-input bk-input-sm"
                                    placeholder="Xonadon"
                                />
                            </div>
                            <div class="bk-address-field">
                                <input
                                    v-model="manualAddress.landmark"
                                    type="text"
                                    class="bk-input"
                                    placeholder="Mo'ljal (ixtiyoriy)"
                                />
                            </div>
                            
                            <!-- Map Button -->
                            <div class="bk-map-actions">
                                <button type="button" class="bk-map-btn" @click="showMap = true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    Xaritadan tanlash
                                </button>
                                <button type="button" class="bk-detect-btn" @click="detectLocation" :disabled="detectingLocation">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    {{ detectingLocation ? 'Aniqlanmoqda...' : 'Joylashuvim' }}
                                </button>
                            </div>
                            
                            <div v-if="manualAddress.lat && manualAddress.lng" class="bk-location-badge">
                                📍 Joylashuv belgilangan
                            </div>
                        </div>
                    </div>
                    
                    <!-- Map Modal -->
                    <div v-if="showMap" class="bk-map-modal">
                        <div class="bk-map-header">
                            <button class="bk-map-close" @click="showMap = false">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                            <span class="bk-map-title">Xaritadan tanlang</span>
                            <button class="bk-map-confirm" @click="confirmMapLocation" :disabled="!mapMarker">
                                Tasdiqlash
                            </button>
                        </div>
                        <div class="bk-map-container">
                            <div ref="mapContainerRef" class="bk-leaflet-map"></div>
                            <div class="bk-map-controls">
                                <button class="bk-detect-location-btn" @click="detectLocation" :disabled="detectingLocation">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    {{ detectingLocation ? 'Aniqlanmoqda...' : 'Mening joylashuvim' }}
                                </button>
                            </div>
                            <p v-if="!mapMarker" class="bk-map-hint">Xaritaga bosib joylashuvni tanlang</p>
                        </div>
                        <div v-if="manualAddress.address" class="bk-map-address">
                            📍 {{ manualAddress.address }}
                        </div>
                    </div>

                    <div class="bk-section" style="margin-top: 16px;">
                        <div class="bk-notes-group">
                            <label class="bk-notes-label">Qo'shimcha izoh (ixtiyoriy)</label>
                            <textarea
                                v-model="booking.notes"
                                class="bk-notes-textarea"
                                placeholder="Masalan: allergiyalarim bor..."
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <div v-if="submitError" class="bk-error">
                        {{ submitError }}
                    </div>
                </div>

                <!-- Cart View -->
                <div v-if="step === 'cart'" class="bk-step-content">
                    <div class="bk-step-header">
                        <h2 class="bk-step-heading">Savatingiz</h2>
                        <p class="bk-step-subheading">{{ cartItemCount }} ta xizmat tanlangan</p>
                    </div>

                    <div v-if="cart.length === 0" class="bk-cart-empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                        </svg>
                        <p>Savat bo'sh</p>
                        <button class="bk-btn-next" @click="step = 1">Xizmat tanlash</button>
                    </div>

                    <div v-else class="bk-cart-items">
                        <div
                            v-for="item in cart"
                            :key="item.id"
                            class="bk-cart-item"
                        >
                            <div class="bk-cart-item-top">
                                <div class="bk-cart-item-service">
                                    <h4>{{ item.service_name }}</h4>
                                    <span class="bk-cart-item-duration">{{ item.duration_minutes }} min</span>
                                </div>
                                <button class="bk-cart-item-remove" @click="removeFromCart(item.id)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="bk-cart-item-details">
                                <div class="bk-cart-item-row">
                                    <span class="bk-cart-item-label">Master</span>
                                    <span class="bk-cart-item-value">{{ item.master_name }}</span>
                                </div>
                                <div class="bk-cart-item-row">
                                    <span class="bk-cart-item-label">Sana</span>
                                    <span class="bk-cart-item-value">{{ item.date_label }}</span>
                                </div>
                                <div class="bk-cart-item-row">
                                    <span class="bk-cart-item-label">Vaqt</span>
                                    <span class="bk-cart-item-value">{{ item.slot_display }}</span>
                                </div>
                                <div class="bk-cart-item-row">
                                    <span class="bk-cart-item-label">Bosim</span>
                                    <span class="bk-cart-item-value">{{ item.pressure_label }}</span>
                                </div>
                                <div class="bk-cart-item-row">
                                    <span class="bk-cart-item-label">Narx</span>
                                    <span class="bk-cart-item-price">{{ formatPrice(item.price) }} so'm</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="submitError" class="bk-error" style="margin-top: 16px;">
                        {{ submitError }}
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="bk-summary-col">
                <div class="bk-summary-card">
                    <h3 class="bk-summary-title">
                        {{ step === 'cart' ? 'Savat' : (step >= 2 ? 'Buyurtma xulosasi' : 'Buyurtma') }}
                    </h3>

                    <!-- Current item summary (steps 1-3) -->
                    <div v-if="step !== 'cart' && selectedService" class="bk-summary-items">
                        <div class="bk-summary-divider" style="margin-top: 0;"></div>

                        <div class="bk-summary-row">
                            <span class="bk-summary-label">{{ getTranslated(getServiceById(selectedService.service_id)?.name) }}</span>
                            <span class="bk-summary-value">{{ getSelectedDuration(selectedService.service_id)?.duration || '—' }} min</span>
                        </div>

                        <div class="bk-summary-row">
                            <span class="bk-summary-label">Bosim</span>
                            <span class="bk-summary-value">{{ pressureLevels.find(p => p.value === booking.pressure_level)?.label }}</span>
                        </div>

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

                    <!-- Cart summary -->
                    <div v-else-if="step === 'cart' && cart.length > 0" class="bk-summary-items">
                        <div class="bk-summary-divider" style="margin-top: 0;"></div>
                        <div v-for="item in cart" :key="'s-' + item.id" class="bk-summary-row">
                            <span class="bk-summary-label">{{ item.service_name }}</span>
                            <span class="bk-summary-value">{{ formatPrice(item.price) }}</span>
                        </div>
                    </div>

                    <div v-else class="bk-summary-empty">
                        Xizmat tanlang
                    </div>

                    <div class="bk-summary-divider"></div>

                    <div class="bk-summary-total">
                        <span class="bk-summary-total-label">Jami</span>
                        <span class="bk-summary-total-value">{{ formatPrice(step === 'cart' ? cartTotal : currentItemPrice) }} so'm</span>
                    </div>

                    <div v-if="step !== 'cart' && step >= 2 && booking.slot" class="bk-summary-row" style="margin-top: 12px;">
                        <span class="bk-summary-label">Vaqt</span>
                        <span class="bk-summary-value">{{ formatSlotRange(booking.slot) }}</span>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="bk-actions">
                    <!-- Steps 1-3 navigation -->
                    <template v-if="step !== 'cart'">
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
                            v-else-if="step === 3"
                            class="bk-btn-next bk-btn-add-cart"
                            :disabled="!canSubmit"
                            @click="addToCart"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                            </svg>
                            Savatga qo'shish
                        </button>
                    </template>

                    <!-- Cart actions -->
                    <template v-else>
                        <button
                            class="bk-btn-back"
                            @click="bookAnotherMaster"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/><polyline points="5,12 12,19 19,12"/>
                            </svg>
                            Boshqa master tanlash
                        </button>
                        <button
                            class="bk-btn-next bk-btn-submit"
                            :disabled="cart.length === 0 || submitting"
                            @click="submitCart"
                        >
                            <span v-if="submitting">Yuborilmoqda...</span>
                            <span v-else>To'lov qilish ({{ formatPrice(cartTotal) }} so'm)</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Styles loaded from resources/css/public/booking.css via app.css -->
