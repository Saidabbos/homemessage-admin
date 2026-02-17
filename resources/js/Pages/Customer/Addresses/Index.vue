<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
    addresses: { type: Array, default: () => [] },
})

// Modal state
const showModal = ref(false)
const editingAddress = ref(null)

const form = useForm({
    name: '',
    address: '',
    entrance: '',
    floor: '',
    apartment: '',
    landmark: '',
    is_default: false,
})

const openAddModal = () => {
    editingAddress.value = null
    form.reset()
    form.is_default = props.addresses.length === 0
    showModal.value = true
}

const openEditModal = (address) => {
    editingAddress.value = address
    form.name = address.name
    form.address = address.address
    form.entrance = address.entrance || ''
    form.floor = address.floor || ''
    form.apartment = address.apartment || ''
    form.landmark = address.landmark || ''
    form.is_default = address.is_default
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    editingAddress.value = null
    form.reset()
    form.clearErrors()
}

const saveAddress = () => {
    if (editingAddress.value) {
        form.put(`/customer/addresses/${editingAddress.value.id}`, {
            onSuccess: () => closeModal(),
        })
    } else {
        form.post('/customer/addresses', {
            onSuccess: () => closeModal(),
        })
    }
}

const deleteAddress = (address) => {
    if (confirm(t('customer.addresses.confirmDelete'))) {
        router.delete(`/customer/addresses/${address.id}`)
    }
}

const setDefault = (address) => {
    router.post(`/customer/addresses/${address.id}/default`)
}

const logout = () => {
    router.post('/customer/logout')
}

const customer = page.props.auth?.user || {}
</script>

<template>
    <Head :title="t('customer.addresses.title')" />

    <div class="cd-page">
        <!-- Sidebar -->
        <aside class="cd-sidebar">
            <div class="cd-sidebar-top">
                <Link href="/" class="cd-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="cd-nav">
                    <Link href="/customer/dashboard" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/masters" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <Link href="/customer/ratings" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('customer.navRatings') }}</span>
                    </Link>
                    <Link href="/customer/favorites" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                    <Link href="/customer/addresses" class="cd-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ t('customer.navAddresses') }}</span>
                    </Link>
                    <Link href="/customer/profile" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('customer.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="cd-sidebar-bottom">
                <div class="cd-sidebar-divider"></div>
                <div class="cd-user-profile">
                    <Link href="/customer/profile" class="cd-user-link">
                        <div class="cd-user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="cd-user-info">
                            <span class="cd-user-name">{{ customer.name }}</span>
                            <span class="cd-user-phone">{{ customer.phone }}</span>
                        </div>
                    </Link>
                    <button class="cd-logout-btn" @click="logout" :title="t('customer.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="cd-main">
            <!-- Top Bar -->
            <div class="cd-topbar">
                <div class="cd-topbar-left">
                    <h1 class="cd-page-title">{{ t('customer.addresses.title') }}</h1>
                    <p class="cd-page-subtitle">{{ t('customer.addresses.subtitle') }}</p>
                </div>
                <div class="cd-topbar-right">
                    <button @click="openAddModal" class="cd-btn cd-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ t('customer.addresses.add') }}
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="cd-content">
                <!-- Empty State -->
                <div v-if="addresses.length === 0" class="cd-empty-state">
                    <div class="cd-empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <h3>{{ t('customer.addresses.empty') }}</h3>
                    <p>{{ t('customer.addresses.emptyHint') }}</p>
                    <button @click="openAddModal" class="cd-btn cd-btn-primary">
                        {{ t('customer.addresses.addFirst') }}
                    </button>
                </div>

                <!-- Addresses Grid -->
                <div v-else class="cd-addresses-grid">
                    <div 
                        v-for="address in addresses" 
                        :key="address.id" 
                        :class="['cd-address-card', { 'cd-address-default': address.is_default }]"
                    >
                        <div class="cd-address-header">
                            <div class="cd-address-name">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>{{ address.name }}</span>
                            </div>
                            <span v-if="address.is_default" class="cd-address-badge">
                                {{ t('customer.addresses.default') }}
                            </span>
                        </div>
                        
                        <div class="cd-address-body">
                            <p class="cd-address-main">{{ address.address }}</p>
                            <div class="cd-address-details" v-if="address.entrance || address.floor || address.apartment">
                                <span v-if="address.entrance">{{ t('customer.addresses.entrance') }}: {{ address.entrance }}</span>
                                <span v-if="address.floor">{{ t('customer.addresses.floor') }}: {{ address.floor }}</span>
                                <span v-if="address.apartment">{{ t('customer.addresses.apartment') }}: {{ address.apartment }}</span>
                            </div>
                            <p v-if="address.landmark" class="cd-address-landmark">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                {{ address.landmark }}
                            </p>
                        </div>

                        <div class="cd-address-actions">
                            <button 
                                v-if="!address.is_default" 
                                @click="setDefault(address)" 
                                class="cd-btn-link"
                            >
                                {{ t('customer.addresses.setDefault') }}
                            </button>
                            <button @click="openEditModal(address)" class="cd-btn-link">
                                {{ t('common.edit') }}
                            </button>
                            <button @click="deleteAddress(address)" class="cd-btn-link cd-btn-danger">
                                {{ t('common.delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Add/Edit Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="cd-modal-overlay" @click.self="closeModal">
                <div class="cd-modal">
                    <div class="cd-modal-header">
                        <h3>{{ editingAddress ? t('customer.addresses.edit') : t('customer.addresses.add') }}</h3>
                        <button @click="closeModal" class="cd-modal-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveAddress" class="cd-modal-body">
                        <!-- Name -->
                        <div class="cd-form-group">
                            <label class="cd-form-label">{{ t('customer.addresses.name') }} *</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="cd-form-input"
                                :class="{ 'cd-form-error': form.errors.name }"
                                :placeholder="t('customer.addresses.namePlaceholder')"
                            />
                            <span v-if="form.errors.name" class="cd-error-text">{{ form.errors.name }}</span>
                        </div>

                        <!-- Address -->
                        <div class="cd-form-group">
                            <label class="cd-form-label">{{ t('customer.addresses.address') }} *</label>
                            <textarea 
                                v-model="form.address" 
                                class="cd-form-textarea"
                                :class="{ 'cd-form-error': form.errors.address }"
                                :placeholder="t('customer.addresses.addressPlaceholder')"
                                rows="2"
                            ></textarea>
                            <span v-if="form.errors.address" class="cd-error-text">{{ form.errors.address }}</span>
                        </div>

                        <!-- Details Row -->
                        <div class="cd-form-row">
                            <div class="cd-form-group">
                                <label class="cd-form-label">{{ t('customer.addresses.entrance') }}</label>
                                <input v-model="form.entrance" type="text" class="cd-form-input" placeholder="1" />
                            </div>
                            <div class="cd-form-group">
                                <label class="cd-form-label">{{ t('customer.addresses.floor') }}</label>
                                <input v-model="form.floor" type="text" class="cd-form-input" placeholder="2" />
                            </div>
                            <div class="cd-form-group">
                                <label class="cd-form-label">{{ t('customer.addresses.apartment') }}</label>
                                <input v-model="form.apartment" type="text" class="cd-form-input" placeholder="15" />
                            </div>
                        </div>

                        <!-- Landmark -->
                        <div class="cd-form-group">
                            <label class="cd-form-label">{{ t('customer.addresses.landmark') }}</label>
                            <input 
                                v-model="form.landmark" 
                                type="text" 
                                class="cd-form-input"
                                :placeholder="t('customer.addresses.landmarkPlaceholder')"
                            />
                        </div>

                        <!-- Default checkbox -->
                        <div class="cd-form-group">
                            <label class="cd-checkbox-label">
                                <input v-model="form.is_default" type="checkbox" class="cd-checkbox" />
                                <span>{{ t('customer.addresses.makeDefault') }}</span>
                            </label>
                        </div>
                    </form>

                    <div class="cd-modal-footer">
                        <button @click="closeModal" class="cd-btn cd-btn-secondary">
                            {{ t('common.cancel') }}
                        </button>
                        <button @click="saveAddress" class="cd-btn cd-btn-primary" :disabled="form.processing">
                            {{ form.processing ? t('common.saving') : t('common.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style>
/* Additional styles for addresses page */
.cd-addresses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}

.cd-address-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 1.25rem;
    transition: all 0.2s ease;
}

.cd-address-card:hover {
    border-color: #d1d5db;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.cd-address-default {
    border-color: #6366f1;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.02) 0%, rgba(99, 102, 241, 0.05) 100%);
}

.cd-address-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.cd-address-name {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1f2937;
}

.cd-address-name svg {
    color: #6366f1;
}

.cd-address-badge {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.1);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.cd-address-body {
    margin-bottom: 1rem;
}

.cd-address-main {
    color: #374151;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}

.cd-address-details {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: #6b7280;
}

.cd-address-landmark {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.85rem;
    color: #9ca3af;
    margin-top: 0.5rem;
}

.cd-address-actions {
    display: flex;
    gap: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.cd-btn-link {
    background: none;
    border: none;
    color: #6366f1;
    font-size: 0.875rem;
    cursor: pointer;
    padding: 0;
}

.cd-btn-link:hover {
    text-decoration: underline;
}

.cd-btn-link.cd-btn-danger {
    color: #ef4444;
}

/* Empty state */
.cd-empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.cd-empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: #f3f4f6;
    border-radius: 50%;
    margin-bottom: 1.5rem;
    color: #9ca3af;
}

.cd-empty-state h3 {
    font-size: 1.25rem;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.cd-empty-state p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

/* Modal styles */
.cd-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.cd-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.cd-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.cd-modal-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.cd-modal-close {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
}

.cd-modal-close:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.cd-modal-body {
    padding: 1.5rem;
}

.cd-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.cd-form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.cd-form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.95rem;
    resize: vertical;
    font-family: inherit;
}

.cd-form-textarea:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.cd-checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    color: #374151;
    font-size: 0.95rem;
}

.cd-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #6366f1;
}

.cd-btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.cd-btn-secondary:hover {
    background: #e5e7eb;
}

.cd-topbar-right {
    display: flex;
    gap: 0.75rem;
}

.cd-page-subtitle {
    font-size: 0.9rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

@media (max-width: 640px) {
    .cd-form-row {
        grid-template-columns: 1fr;
    }
    
    .cd-addresses-grid {
        grid-template-columns: 1fr;
    }
}
</style>
