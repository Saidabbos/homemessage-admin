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
            
            // Update map
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
        <Teleport to="body">
            <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                <div class="modal">
                    <div class="modal-header">
                        <h3>{{ editingAddress ? 'Manzilni tahrirlash' : 'Manzil qo\'shish' }}</h3>
                        <button @click="closeModal" class="close-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveAddress" class="modal-body">
                        <!-- Name -->
                        <div class="form-group">
                            <label>Nomi *</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                placeholder="masalan: Uy, Ish"
                                :class="{ error: form.errors.name }"
                            />
                            <span v-if="form.errors.name" class="error-text">{{ form.errors.name }}</span>
                        </div>

                        <!-- Address -->
                        <div class="form-group">
                            <label>Manzil *</label>
                            <textarea 
                                v-model="form.address" 
                                placeholder="To'liq manzilni kiriting"
                                rows="2"
                                :class="{ error: form.errors.address }"
                            ></textarea>
                            <span v-if="form.errors.address" class="error-text">{{ form.errors.address }}</span>
                        </div>

                        <!-- Details Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kirish</label>
                                <input v-model="form.entrance" type="text" placeholder="1" />
                            </div>
                            <div class="form-group">
                                <label>Qavat</label>
                                <input v-model="form.floor" type="text" placeholder="2" />
                            </div>
                            <div class="form-group">
                                <label>Xonadon</label>
                                <input v-model="form.apartment" type="text" placeholder="15" />
                            </div>
                        </div>

                        <!-- Landmark -->
                        <div class="form-group">
                            <label>Mo'ljal</label>
                            <input v-model="form.landmark" type="text" placeholder="Yaqin joylashgan ob'ekt" />
                        </div>

                        <!-- Map -->
                        <div class="form-group">
                            <div class="map-header">
                                <label>Joylashuv</label>
                                <button type="button" @click="detectLocation" class="detect-btn" :disabled="locating">
                                    <svg v-if="!locating" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <circle cx="12" cy="12" r="3"/>
                                        <line x1="12" y1="2" x2="12" y2="6"/>
                                        <line x1="12" y1="18" x2="12" y2="22"/>
                                        <line x1="2" y1="12" x2="6" y2="12"/>
                                        <line x1="18" y1="12" x2="22" y2="12"/>
                                    </svg>
                                    <span v-if="locating" class="loading-spinner"></span>
                                    {{ locating ? 'Aniqlanmoqda...' : 'Mening joylashuvim' }}
                                </button>
                            </div>
                            <div ref="mapContainer" class="map-container"></div>
                            <p v-if="locationError" class="location-error">{{ locationError }}</p>
                            <div v-if="form.latitude && form.longitude" class="location-info">
                                <span>📍 {{ form.latitude.toFixed(5) }}, {{ form.longitude.toFixed(5) }}</span>
                                <button type="button" @click="clearLocation" class="clear-btn">Tozalash</button>
                            </div>
                        </div>

                        <!-- Default checkbox -->
                        <label class="checkbox-label">
                            <input v-model="form.is_default" type="checkbox" />
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
        </Teleport>
    </div>
</template>

<style scoped>
.addresses-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
    color: #fff;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 10;
}

.back-btn, .add-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 10px;
    color: #fff;
    cursor: pointer;
}

.header-title {
    font-size: 18px;
    font-weight: 600;
}

.content {
    padding: 20px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.5);
}

.empty-state h3 {
    font-size: 18px;
    margin-bottom: 8px;
}

.empty-state p {
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 24px;
}

/* Address List */
.address-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.address-card {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.address-card.is-default {
    border-color: #C8A951;
    background: rgba(200, 169, 81, 0.1);
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
}

.address-name svg {
    color: #C8A951;
}

.default-badge {
    font-size: 12px;
    padding: 4px 10px;
    background: rgba(200, 169, 81, 0.2);
    color: #C8A951;
    border-radius: 20px;
}

.address-text {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    margin-bottom: 8px;
}

.address-details {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 8px;
}

.address-landmark, .address-coords {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
}

.address-coords {
    color: #C8A951;
}

.address-actions {
    display: flex;
    gap: 12px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.action-btn {
    background: none;
    border: none;
    color: #C8A951;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
}

.action-btn.danger {
    color: #ef4444;
}

/* Buttons */
.primary-btn {
    background: linear-gradient(135deg, #C8A951 0%, #B8983F 100%);
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.primary-btn:disabled {
    opacity: 0.6;
}

.secondary-btn {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: flex-end;
    z-index: 100;
}

.modal {
    background: #1a1a2e;
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
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
    font-size: 18px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    cursor: pointer;
    padding: 4px;
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
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-footer button {
    flex: 1;
}

/* Form */
.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 8px;
}

.form-group label .hint {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
}

.form-group input,
.form-group textarea {
    width: 100%;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 12px 14px;
    color: #fff;
    font-size: 14px;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #C8A951;
}

.form-group input.error,
.form-group textarea.error {
    border-color: #ef4444;
}

.error-text {
    font-size: 12px;
    color: #ef4444;
    margin-top: 4px;
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
    cursor: pointer;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    accent-color: #C8A951;
}

/* Map */
.map-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.map-header label {
    margin-bottom: 0;
}

.detect-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(200, 169, 81, 0.2);
    border: 1px solid rgba(200, 169, 81, 0.3);
    color: #C8A951;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.detect-btn:hover:not(:disabled) {
    background: rgba(200, 169, 81, 0.3);
}

.detect-btn:disabled {
    opacity: 0.7;
    cursor: wait;
}

.loading-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(200, 169, 81, 0.3);
    border-top-color: #C8A951;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.map-container {
    height: 200px;
    border-radius: 10px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.1);
}

.location-error {
    color: #ef4444;
    font-size: 12px;
    margin-top: 6px;
}

.location-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
    padding: 8px 12px;
    background: rgba(200, 169, 81, 0.15);
    border-radius: 8px;
    font-size: 13px;
}

.clear-btn {
    background: none;
    border: none;
    color: #ef4444;
    font-size: 13px;
    cursor: pointer;
}
</style>
