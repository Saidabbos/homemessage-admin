<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';
import { useCart } from '@/composables/useCart';

const { t } = useI18n();

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

// Cart state (persisted via localStorage)
const { cart, cartTotal, cartItemCount, clearCart } = useCart();

// Booking data
const booking = ref({
    master_id: null,
    date: null,
    slot: null,
    pressure_level: null,
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
    lat: null,
    lng: null,
});

// Map & Geolocation
const showMap = ref(false);
const detectingLocation = ref(false);
const mapCenter = ref([41.2995, 69.2401]); // Tashkent default
const mapMarker = ref(null);
const mapContainerRef = ref(null);
let leafletMap = null;
let leafletMarker = null;

// Initialize Leaflet map when modal opens
const initLeafletMap = async () => {
    await nextTick();
    if (!mapContainerRef.value || leafletMap) return;
    
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    
    // Fix Leaflet marker icons
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
    
    // Add existing marker if any
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
    
    // Click on map to add/move marker
    leafletMap.on('click', async (e) => {
        const { lat, lng } = e.latlng;
        mapMarker.value = [lat, lng];
        manualAddress.value.lat = lat;
        manualAddress.value.lng = lng;
        
        if (leafletMarker) {
            leafletMarker.setLatLng([lat, lng]);
        } else {
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

// Cleanup map when modal closes
const cleanupMap = () => {
    if (leafletMap) {
        leafletMap.remove();
        leafletMap = null;
        leafletMarker = null;
    }
};

// Watch showMap to init/cleanup
watch(showMap, async (visible) => {
    if (visible) {
        await nextTick();
        initLeafletMap();
    } else {
        cleanupMap();
    }
});

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
        
        // Update map if already open
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
        
        // Reverse geocode
        await reverseGeocode(latitude, longitude);
        
        // Open map if not already open
        if (!showMap.value) {
            showMap.value = true;
        }
    } catch (error) {
        console.error('Location error:', error);
        alert('Joylashuvni aniqlab bo\'lmadi. Ruxsat berilganligini tekshiring.');
    } finally {
        detectingLocation.value = false;
    }
};

const reverseGeocode = async (lat, lng) => {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=uz`
        );
        const data = await response.json();
        
        if (data.display_name) {
            // Extract meaningful address parts
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

const onMapClick = async (lat, lng) => {
    mapMarker.value = [lat, lng];
    manualAddress.value.lat = lat;
    manualAddress.value.lng = lng;
    await reverseGeocode(lat, lng);
};

const confirmMapLocation = () => {
    showMap.value = false;
};

// Save Address
const savingAddress = ref(false);

const saveAddress = async () => {
    if (!manualAddress.value.address) {
        console.log('No address to save');
        return;
    }
    
    savingAddress.value = true;
    console.log('Saving address:', manualAddress.value);
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        console.log('CSRF Token:', csrfToken ? 'Found' : 'Missing');
        
        // Generate unique name based on existing addresses count
        const existingCount = savedAddresses.value.length;
        const addressName = existingCount === 0 ? 'Manzil 1' : `Manzil ${existingCount + 1}`;
        
        const payload = {
            name: addressName,
            address: manualAddress.value.address,
            entrance: manualAddress.value.entrance || '',
            floor: manualAddress.value.floor || '',
            apartment: manualAddress.value.apartment || '',
            landmark: manualAddress.value.landmark || '',
            lat: manualAddress.value.lat,
            lng: manualAddress.value.lng,
        };
        console.log('Payload:', payload);
        
        const response = await fetch('/api/user/addresses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });
        
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success || data.address) {
            // Reload addresses and select the new one
            await loadAddresses();
            const newAddr = savedAddresses.value.find(a => a.address === manualAddress.value.address);
            if (newAddr) {
                selectedAddressId.value = newAddr.id;
            }
            showManualAddress.value = false;
            // Reset form
            manualAddress.value = { address: '', entrance: '', floor: '', apartment: '', landmark: '', lat: null, lng: null };
        }
    } catch (e) {
        console.error('Failed to save address:', e);
        alert('Manzilni saqlashda xatolik: ' + e.message);
    } finally {
        savingAddress.value = false;
    }
};

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
    
    // Check if coming from cart button in Home
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('view') === 'cart' && cart.value.length > 0) {
        step.value = 'cart';
    }
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

// cartTotal and cartItemCount come from useCart composable

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

// ==================== Flat Slots ====================

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

const flatSlots = computed(() => {
    const blocked = cartBlockedSlots.value;
    return availableSlots.value.map(slot => {
        const isInCart = blocked.has(slot.start);
        return {
            ...slot,
            disabled: slot.disabled || isInCart,
            inCart: isInCart,
        };
    });
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
    selectedService.value?.service_id && selectedService.value?.duration_id && booking.value.pressure_level
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
    booking.value = { master_id: null, date: null, slot: null, pressure_level: null, notes: '' };
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
            // Clear cart after successful submission
            clearCart();
            // If payment is enabled, go directly to payment page
            if (props.payment?.enabled) {
                window.location.href = `/booking/payment/${data.group_id}?source=miniapp`;
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
                <h2 class="bk-section-title">{{ t('booking.master_arrival_time') }}</h2>
                <div v-if="!booking.master_id" class="bk-empty">Avval masterni tanlang</div>
                <div v-else-if="!booking.date" class="bk-empty">Avval sanani tanlang</div>
                <div v-else-if="loadingSlots" class="bk-loading">
                    <div class="bk-spinner"></div>
                </div>
                <div v-else-if="availableSlots.length === 0" class="bk-empty">Bu kunga bo'sh vaqt yo'q</div>
                <div v-else class="bk-slots-slider-wrap">
                    <div class="bk-slots-slider">
                        <button
                            v-for="slot in flatSlots"
                            :key="slot.start"
                            class="bk-slot-chip"
                            :class="{ selected: booking.slot === slot.start, disabled: slot.disabled, 'in-cart': slot.inCart }"
                            :disabled="slot.disabled"
                            @click="booking.slot = slot.start"
                        >
                            {{ slot.start.slice(0, 5) }}
                        </button>
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

                        <!-- Location Detection Button -->
                        <div class="bk-location-btns">
                            <button 
                                class="bk-detect-location" 
                                @click="detectLocation"
                                :disabled="detectingLocation"
                            >
                                <svg v-if="!detectingLocation" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <circle cx="12" cy="12" r="3"/>
                                    <line x1="12" y1="2" x2="12" y2="6"/>
                                    <line x1="12" y1="18" x2="12" y2="22"/>
                                    <line x1="2" y1="12" x2="6" y2="12"/>
                                    <line x1="18" y1="12" x2="22" y2="12"/>
                                </svg>
                                <div v-else class="bk-spinner-small"></div>
                                {{ detectingLocation ? 'Aniqlanmoqda...' : 'Joylashuvni aniqlash' }}
                            </button>
                            <button 
                                class="bk-show-map"
                                @click="showMap = true"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                Xaritadan tanlash
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
                            
                            <!-- Save Address Button -->
                            <button 
                                type="button"
                                class="bk-save-address-btn"
                                :disabled="!manualAddress.address || savingAddress"
                                @click="saveAddress"
                            >
                                <svg v-if="!savingAddress" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20,6 9,17 4,12"/>
                                </svg>
                                <div v-else class="bk-spinner-small"></div>
                                {{ savingAddress ? 'Saqlanmoqda...' : 'Manzilni saqlash' }}
                            </button>
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
                        <!-- Interactive Leaflet Map -->
                        <div ref="mapContainerRef" class="bk-leaflet-map"></div>
                        
                        <!-- Detect Location Button -->
                        <div class="bk-map-controls">
                            <button class="bk-detect-btn" @click="detectLocation" :disabled="detectingLocation">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <circle cx="12" cy="12" r="3"/>
                                    <line x1="12" y1="2" x2="12" y2="6"/>
                                    <line x1="12" y1="18" x2="12" y2="22"/>
                                    <line x1="2" y1="12" x2="6" y2="12"/>
                                    <line x1="18" y1="12" x2="22" y2="12"/>
                                </svg>
                                {{ detectingLocation ? 'Aniqlanmoqda...' : 'Mening joylashuvim' }}
                            </button>
                        </div>
                        
                        <!-- Hint -->
                        <p v-if="!mapMarker" class="bk-map-hint">Xaritaga bosib joylashuvni tanlang</p>
                    </div>
                    <div v-if="manualAddress.address" class="bk-map-address">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ manualAddress.address }}
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

/* Master List - Horizontal Slider */
.bk-master-list {
    display: flex;
    flex-direction: row;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.bk-master-list::-webkit-scrollbar {
    display: none;
}

.bk-master-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 14px 12px;
    min-width: calc(50% - 6px);
    max-width: calc(50% - 6px);
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.6);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s;
    scroll-snap-align: start;
    flex-shrink: 0;
}

.bk-master-card:hover {
    background: rgba(255,255,255,0.6);
}

.bk-master-card.selected {
    background: rgba(200,169,81,0.15);
    border-color: var(--gold);
    box-shadow: 0 4px 12px rgba(200,169,81,0.2);
}

.bk-master-photo {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--gold);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 18px;
}

.bk-master-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bk-master-info {
    text-align: center;
    width: 100%;
}

.bk-master-name {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.bk-master-rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    margin-top: 4px;
}

.bk-master-rating svg {
    width: 10px;
    height: 10px;
}

.bk-master-rating span {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-muted);
    margin-left: 2px;
}

.bk-master-check {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 22px;
    height: 22px;
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

.bk-slot-btn.in-cart {
    background: rgba(200, 169, 81, 0.15);
    border-color: var(--gold);
    opacity: 0.6;
}

.slot-cart-icon {
    font-size: 10px;
    margin-left: 4px;
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

/* Location Buttons */
.bk-location-btns {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

.bk-detect-location,
.bk-show-map {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border: 2px dashed rgba(200,169,81,0.4);
    border-radius: 12px;
    background: rgba(200,169,81,0.05);
    color: var(--gold);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-detect-location:hover,
.bk-show-map:hover {
    background: rgba(200,169,81,0.1);
    border-color: var(--gold);
}

.bk-detect-location:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.bk-spinner-small {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(200,169,81,0.3);
    border-top-color: var(--gold);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

/* Map Modal */
.bk-map-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: white;
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.bk-map-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--cream);
    border-bottom: 1px solid rgba(200,169,81,0.2);
}

.bk-map-close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: rgba(0,0,0,0.05);
    border-radius: 50%;
    color: var(--navy);
    cursor: pointer;
}

.bk-map-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--navy);
}

.bk-map-confirm {
    padding: 8px 16px;
    background: var(--gold);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.bk-map-confirm:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.bk-map-container {
    flex: 1;
    position: relative;
    background: #f0f0f0;
    min-height: 300px;
}

.bk-leaflet-map {
    width: 100%;
    height: 100%;
    min-height: 300px;
}

.bk-map-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
}

.bk-map-hint {
    position: absolute;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 16px;
    background: rgba(0,0,0,0.7);
    border-radius: 20px;
    color: white;
    font-size: 12px;
    text-align: center;
    z-index: 1000;
    white-space: nowrap;
}

.bk-detect-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: white;
    border: none;
    border-radius: 24px;
    color: var(--navy);
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    cursor: pointer;
}

.bk-detect-btn:disabled {
    opacity: 0.6;
    cursor: wait;
}

.bk-detect-btn svg {
    color: var(--gold);
}

.bk-map-address {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px;
    background: var(--cream);
    border-top: 1px solid rgba(200,169,81,0.2);
    font-size: 14px;
    color: var(--navy);
}

/* Save Address Button */
.bk-save-address-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px;
    margin-top: 8px;
    background: var(--gold);
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.bk-save-address-btn:hover {
    background: #b89841;
}

.bk-save-address-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
