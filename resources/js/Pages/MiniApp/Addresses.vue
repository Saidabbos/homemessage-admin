<script setup>
import { ref, nextTick } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    addresses: { type: Array, default: () => [] },
});

// Modal state
const showModal = ref(false);
const editingAddress = ref(null);
const mapContainer = ref(null);
let map = null;
let marker = null;

// Default center: Tashkent
const defaultCenter = [41.2995, 69.2401];

const form = useForm({
    name: '',
    address: '',
    entrance: '',
    floor: '',
    apartment: '',
    landmark: '',
    latitude: null,
    longitude: null,
    is_default: false,
});

const openAddModal = () => {
    editingAddress.value = null;
    form.reset();
    form.is_default = props.addresses.length === 0;
    form.latitude = null;
    form.longitude = null;
    showModal.value = true;
    nextTick(() => initMap());
};

const openEditModal = (address) => {
    editingAddress.value = address;
    form.name = address.name;
    form.address = address.address;
    form.entrance = address.entrance || '';
    form.floor = address.floor || '';
    form.apartment = address.apartment || '';
    form.landmark = address.landmark || '';
    form.latitude = address.latitude;
    form.longitude = address.longitude;
    form.is_default = address.is_default;
    showModal.value = true;
    nextTick(() => initMap());
};

const closeModal = () => {
    showModal.value = false;
    editingAddress.value = null;
    form.reset();
    form.clearErrors();
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
};

const saveAddress = () => {
    if (editingAddress.value) {
        form.put(`/app/addresses/${editingAddress.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/app/addresses', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteAddress = (address) => {
    if (confirm('Haqiqatan ham bu manzilni o\'chirmoqchimisiz?')) {
        router.delete(`/app/addresses/${address.id}`);
    }
};

const setDefault = (address) => {
    router.post(`/app/addresses/${address.id}/default`);
};

// Map functions
const initMap = async () => {
    await nextTick();
    if (!mapContainer.value) return;
    
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
    
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });
    
    const center = form.latitude && form.longitude 
        ? [form.latitude, form.longitude] 
        : defaultCenter;
    
    map = L.map(mapContainer.value).setView(center, 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    if (form.latitude && form.longitude) {
        marker = L.marker([form.latitude, form.longitude], { draggable: true }).addTo(map);
        marker.on('dragend', onMarkerDrag);
    }
    
    map.on('click', onMapClick);
};

const onMapClick = async (e) => {
    const L = await import('leaflet');
    form.latitude = e.latlng.lat;
    form.longitude = e.latlng.lng;
    
    if (marker) {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng, { draggable: true }).addTo(map);
        marker.on('dragend', onMarkerDrag);
    }
};

const onMarkerDrag = (e) => {
    const latlng = e.target.getLatLng();
    form.latitude = latlng.lat;
    form.longitude = latlng.lng;
};

const clearLocation = () => {
    form.latitude = null;
    form.longitude = null;
    if (marker) {
        marker.remove();
        marker = null;
    }
};

// Geolocation
const locating = ref(false);
const locationError = ref('');

const detectLocation = async () => {
    if (!navigator.geolocation) {
        locationError.value = 'Geolokatsiya qo\'llab-quvvatlanmaydi';
        return;
    }
    
    locating.value = true;
    locationError.value = '';
    
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            form.latitude = latitude;
            form.longitude = longitude;
            
            if (map) {
                const L = await import('leaflet');
                map.setView([latitude, longitude], 16);
                
                if (marker) {
                    marker.setLatLng([latitude, longitude]);
                } else {
                    marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
                    marker.on('dragend', onMarkerDrag);
                }
            }
            
            locating.value = false;
        },
        (error) => {
            locating.value = false;
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = 'Joylashuvga ruxsat berilmadi';
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = 'Joylashuv aniqlanmadi';
                    break;
                case error.TIMEOUT:
                    locationError.value = 'Vaqt tugadi, qayta urinib ko\'ring';
                    break;
                default:
                    locationError.value = 'Xatolik yuz berdi';
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
};
</script>

<template>
    <div class="addresses-page">
        <!-- Header -->
        <header class="page-header">
            <Link href="/app" class="back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </Link>
            <h1 class="header-title">Manzillarim</h1>
            <button @click="openAddModal" class="add-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </button>
        </header>

        <!-- Content -->
        <div class="content">
            <!-- Empty State -->
            <div v-if="addresses.length === 0" class="empty-state">
                <div class="empty-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <h3>Manzillar yo'q</h3>
                <p>Qulay buyurtma berish uchun manzillaringizni saqlang</p>
                <button @click="openAddModal" class="primary-btn">
                    Manzil qo'shish
                </button>
            </div>

            <!-- Address List -->
            <div v-else class="address-list">
                <div 
                    v-for="address in addresses" 
                    :key="address.id" 
                    :class="['address-card', { 'is-default': address.is_default }]"
                >
                    <div class="address-header">
                        <div class="address-name">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>{{ address.name }}</span>
                        </div>
                        <span v-if="address.is_default" class="default-badge">Asosiy</span>
                    </div>

                    <p class="address-text">{{ address.address }}</p>
                    
                    <div v-if="address.entrance || address.floor || address.apartment" class="address-details">
                        <span v-if="address.entrance">Kirish: {{ address.entrance }}</span>
                        <span v-if="address.floor">Qavat: {{ address.floor }}</span>
                        <span v-if="address.apartment">Xonadon: {{ address.apartment }}</span>
                    </div>

                    <p v-if="address.landmark" class="address-landmark">
                        📍 {{ address.landmark }}
                    </p>

                    <p v-if="address.latitude && address.longitude" class="address-coords">
                        🗺️ Xaritada belgilangan
                    </p>

                    <div class="address-actions">
                        <button v-if="!address.is_default" @click="setDefault(address)" class="action-btn">
                            Asosiy qilish
                        </button>
                        <button @click="openEditModal(address)" class="action-btn">
                            Tahrirlash
                        </button>
                        <button @click="deleteAddress(address)" class="action-btn danger">
                            O'chirish
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal">
                <div class="modal-header">
                    <h3>{{ editingAddress ? 'Manzilni tahrirlash' : 'Manzil qo\'shish' }}</h3>
                    <button @click="closeModal" class="modal-close">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveAddress" class="modal-body">
                    <!-- Name -->
                    <div class="form-group">
                        <label class="form-label">Nomi *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="form-input"
                            :class="{ error: form.errors.name }"
                            placeholder="masalan: Uy, Ish"
                        />
                        <span v-if="form.errors.name" class="error-text">{{ form.errors.name }}</span>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label class="form-label">Manzil *</label>
                        <textarea 
                            v-model="form.address" 
                            class="form-input form-textarea"
                            :class="{ error: form.errors.address }"
                            placeholder="To'liq manzilni kiriting"
                            rows="2"
                        ></textarea>
                        <span v-if="form.errors.address" class="error-text">{{ form.errors.address }}</span>
                    </div>

                    <!-- Details Row -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Kirish</label>
                            <input v-model="form.entrance" type="text" class="form-input" placeholder="1" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Qavat</label>
                            <input v-model="form.floor" type="text" class="form-input" placeholder="2" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Xonadon</label>
                            <input v-model="form.apartment" type="text" class="form-input" placeholder="15" />
                        </div>
                    </div>

                    <!-- Landmark -->
                    <div class="form-group">
                        <label class="form-label">Mo'ljal</label>
                        <input v-model="form.landmark" type="text" class="form-input" placeholder="Yaqin joylashgan ob'ekt" />
                    </div>

                    <!-- Map -->
                    <div class="form-group">
                        <div class="map-header">
                            <label class="form-label">Joylashuv</label>
                            <button type="button" @click="detectLocation" class="detect-btn" :disabled="locating">
                                <svg v-if="!locating" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <circle cx="12" cy="12" r="3"/>
                                    <line x1="12" y1="2" x2="12" y2="6"/>
                                    <line x1="12" y1="18" x2="12" y2="22"/>
                                    <line x1="2" y1="12" x2="6" y2="12"/>
                                    <line x1="18" y1="12" x2="22" y2="12"/>
                                </svg>
                                <span v-if="locating" class="loading-spinner"></span>
                                {{ locating ? 'Aniqlanmoqda...' : 'Joylashuvim' }}
                            </button>
                        </div>
                        <div ref="mapContainer" class="map-container"></div>
                        <p v-if="locationError" class="error-text">{{ locationError }}</p>
                        <div v-if="form.latitude && form.longitude" class="location-info">
                            <span>📍 {{ form.latitude.toFixed(5) }}, {{ form.longitude.toFixed(5) }}</span>
                            <button type="button" @click="clearLocation" class="clear-btn">Tozalash</button>
                        </div>
                    </div>

                    <!-- Default checkbox -->
                    <label class="checkbox-label">
                        <input v-model="form.is_default" type="checkbox" class="checkbox" />
                        <span>Asosiy manzil sifatida belgilash</span>
                    </label>
                </form>

                <div class="modal-footer">
                    <button @click="closeModal" class="secondary-btn">Bekor qilish</button>
                    <button @click="saveAddress" class="primary-btn" :disabled="form.processing">
                        {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Toast -->
        <div v-if="$page.props.flash?.success" class="toast-success">
            {{ $page.props.flash.success }}
        </div>
    </div>
</template>

<style scoped>
.addresses-page {
    --navy: #1B2B5A;
    --gold: #C8A951;
    --cream: #F5F2ED;
    --cream-dark: #EDE8DF;
    --text-muted: #8B8680;

    min-height: 100vh;
    background: var(--cream);
    font-family: 'Manrope', -apple-system, sans-serif;
    padding-bottom: 40px;
}

/* Header */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: white;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    position: sticky;
    top: 0;
    z-index: 50;
}

.back-btn, .add-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cream);
    border: none;
    border-radius: 12px;
    color: var(--navy);
    cursor: pointer;
    text-decoration: none;
}

.add-btn {
    background: var(--gold);
    color: white;
}

.header-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--navy);
}

/* Content */
.content {
    padding: 16px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: var(--cream);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
}

.empty-state h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 8px;
}

.empty-state p {
    color: var(--text-muted);
    font-size: 14px;
    margin: 0 0 24px;
}

/* Address List */
.address-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.address-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.address-card.is-default {
    border: 2px solid var(--gold);
}

.address-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.address-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: var(--navy);
}

.address-name svg {
    color: var(--gold);
}

.default-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    background: linear-gradient(135deg, var(--gold), #D4B96A);
    color: white;
    border-radius: 20px;
}

.address-text {
    color: var(--navy);
    font-size: 14px;
    margin: 0 0 8px;
    line-height: 1.4;
}

.address-details {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 8px;
}

.address-landmark {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
}

.address-coords {
    font-size: 13px;
    color: var(--gold);
    margin: 4px 0 0;
}

.address-actions {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--cream);
}

.action-btn {
    background: none;
    border: none;
    color: var(--navy);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
}

.action-btn:hover {
    color: var(--gold);
}

.action-btn.danger {
    color: #EF4444;
}

/* Buttons */
.primary-btn {
    background: var(--gold);
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.primary-btn:disabled {
    opacity: 0.6;
}

.secondary-btn {
    background: var(--cream);
    color: var(--navy);
    border: none;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: flex-end;
    z-index: 100;
}

.modal {
    background: white;
    width: 100%;
    max-height: 90vh;
    border-radius: 20px 20px 0 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--cream);
}

.modal-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
    margin: 0;
}

.modal-close {
    background: var(--cream);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    cursor: pointer;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    display: flex;
    gap: 12px;
    padding: 16px 20px;
    border-top: 1px solid var(--cream);
    background: white;
}

.modal-footer button {
    flex: 1;
}

/* Form */
.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 12px 14px;
    background: var(--cream);
    border: 1px solid transparent;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: var(--navy);
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--gold);
    background: white;
}

.form-input.error {
    border-color: #EF4444;
}

.form-textarea {
    resize: vertical;
    min-height: 60px;
}

.form-input::placeholder {
    color: var(--text-muted);
}

.error-text {
    font-size: 12px;
    color: #EF4444;
    margin: 4px 0 0;
    display: block;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--navy);
    cursor: pointer;
}

.checkbox {
    width: 20px;
    height: 20px;
    accent-color: var(--gold);
}

/* Map */
.map-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.map-header .form-label {
    margin-bottom: 0;
}

.detect-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--cream);
    border: 1px solid var(--gold);
    color: var(--navy);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.detect-btn svg {
    color: var(--gold);
}

.detect-btn:hover:not(:disabled) {
    background: rgba(200, 169, 81, 0.1);
}

.detect-btn:disabled {
    opacity: 0.7;
    cursor: wait;
}

.loading-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid var(--cream-dark);
    border-top-color: var(--gold);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.map-container {
    height: 180px;
    border-radius: 12px;
    overflow: hidden;
    background: var(--cream);
}

.location-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
    padding: 10px 12px;
    background: rgba(200, 169, 81, 0.1);
    border-radius: 8px;
    font-size: 13px;
    color: var(--navy);
}

.clear-btn {
    background: none;
    border: none;
    color: #EF4444;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
}

/* Toast */
.toast-success {
    position: fixed;
    bottom: 100px;
    left: 50%;
    transform: translateX(-50%);
    background: #10B981;
    color: white;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 200;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}
</style>
