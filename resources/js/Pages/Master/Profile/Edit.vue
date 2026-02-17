<script setup>
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps({
    master: { type: Object, required: true },
    user: { type: Object, required: true },
})

const form = useForm({
    first_name: props.master.first_name || '',
    last_name: props.master.last_name || '',
    email: props.master.email || '',
    bio_uz: props.master.bio_uz || '',
    bio_ru: props.master.bio_ru || '',
    locale: props.user.locale || 'uz',
    photo: null,
})

const photoPreview = ref(props.master.photo_url)
const photoInput = ref(null)

const selectPhoto = () => {
    photoInput.value.click()
}

const handlePhotoChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.photo = file
        photoPreview.value = URL.createObjectURL(file)
    }
}

const submit = () => {
    form.post('/master/profile', {
        preserveScroll: true,
        forceFormData: true,
        _method: 'PUT',
    })
}

const logout = () => {
    router.post('/master/logout')
}
</script>

<template>
    <Head :title="t('master.profile.editTitle')" />

    <div class="md-page">
        <!-- Sidebar -->
        <aside class="md-sidebar">
            <div class="md-sidebar-top">
                <Link href="/" class="md-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="md-nav">
                    <Link href="/master/dashboard" class="md-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('master.navDashboard') }}</span>
                    </Link>
                    <Link href="/master/orders" class="md-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('master.navOrders') }}</span>
                    </Link>
                    <Link href="/master/ratings" class="md-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('master.navRatings') }}</span>
                    </Link>
                    <Link href="/master/profile" class="md-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('master.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="md-sidebar-bottom">
                <div class="md-sidebar-divider"></div>
                <div class="md-user-profile">
                    <Link href="/master/profile" class="md-user-link">
                        <div class="md-user-avatar">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.first_name" />
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="md-user-info">
                            <span class="md-user-name">{{ master.first_name }} {{ master.last_name }}</span>
                            <span class="md-user-phone">{{ master.phone }}</span>
                        </div>
                    </Link>
                    <button class="md-logout-btn" @click="logout" :title="t('master.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="md-main">
            <!-- Top Bar -->
            <div class="md-topbar">
                <div class="md-topbar-left">
                    <h1 class="md-greeting">{{ t('master.profile.editTitle') }}</h1>
                    <p class="md-date">{{ t('master.profile.editSubtitle') }}</p>
                </div>
                <Link href="/master/profile" class="md-back-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    {{ t('common.back') }}
                </Link>
            </div>

            <!-- Content Area -->
            <div class="md-content md-content-single">
                <div class="md-profile-container">
                    <!-- Photo Card -->
                    <div class="md-card">
                        <h3 class="md-card-title">{{ t('master.profile.photo') }}</h3>
                        <div class="md-photo-upload">
                            <div class="md-photo-preview" @click="selectPhoto">
                                <img v-if="photoPreview" :src="photoPreview" alt="Photo" />
                                <div v-else class="md-photo-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div class="md-photo-overlay">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </div>
                            </div>
                            <input
                                ref="photoInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handlePhotoChange"
                            />
                            <p class="md-photo-hint">{{ t('master.profile.photoHint') }}</p>
                        </div>
                    </div>

                    <!-- Basic Info Card -->
                    <div class="md-card">
                        <h3 class="md-card-title">{{ t('master.profile.basicInfo') }}</h3>
                        
                        <form @submit.prevent="submit" class="md-profile-form">
                            <div class="md-form-grid">
                                <!-- First Name -->
                                <div class="md-form-group">
                                    <label class="md-form-label">{{ t('master.profile.firstName') }} *</label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        class="md-form-input"
                                        :class="{ 'md-form-error': form.errors.first_name }"
                                    />
                                    <p v-if="form.errors.first_name" class="md-error-text">{{ form.errors.first_name }}</p>
                                </div>

                                <!-- Last Name -->
                                <div class="md-form-group">
                                    <label class="md-form-label">{{ t('master.profile.lastName') }} *</label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        class="md-form-input"
                                        :class="{ 'md-form-error': form.errors.last_name }"
                                    />
                                    <p v-if="form.errors.last_name" class="md-error-text">{{ form.errors.last_name }}</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="md-form-group">
                                <label class="md-form-label">{{ t('master.profile.email') }}</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="md-form-input"
                                    :class="{ 'md-form-error': form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="md-error-text">{{ form.errors.email }}</p>
                            </div>

                            <!-- Phone (readonly) -->
                            <div class="md-form-group">
                                <label class="md-form-label">{{ t('master.profile.phone') }}</label>
                                <input
                                    :value="master.phone"
                                    type="tel"
                                    disabled
                                    class="md-form-input md-form-disabled"
                                />
                                <p class="md-hint-text">{{ t('master.profile.phoneHint') }}</p>
                            </div>

                            <!-- Language -->
                            <div class="md-form-group">
                                <label class="md-form-label">{{ t('profile.language') }}</label>
                                <select v-model="form.locale" class="md-form-select">
                                    <option value="uz">O'zbekcha</option>
                                    <option value="ru">Русский</option>
                                    <option value="en">English</option>
                                </select>
                            </div>

                            <!-- Bio UZ -->
                            <div class="md-form-group">
                                <label class="md-form-label">{{ t('master.profile.bio') }} (O'zbekcha)</label>
                                <textarea
                                    v-model="form.bio_uz"
                                    rows="3"
                                    class="md-form-textarea"
                                    :class="{ 'md-form-error': form.errors.bio_uz }"
                                    :placeholder="t('master.profile.bioPlaceholder')"
                                ></textarea>
                                <p v-if="form.errors.bio_uz" class="md-error-text">{{ form.errors.bio_uz }}</p>
                            </div>

                            <!-- Bio RU -->
                            <div class="md-form-group">
                                <label class="md-form-label">{{ t('master.profile.bio') }} (Русский)</label>
                                <textarea
                                    v-model="form.bio_ru"
                                    rows="3"
                                    class="md-form-textarea"
                                    :class="{ 'md-form-error': form.errors.bio_ru }"
                                    :placeholder="t('master.profile.bioPlaceholder')"
                                ></textarea>
                                <p v-if="form.errors.bio_ru" class="md-error-text">{{ form.errors.bio_ru }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="md-form-actions">
                                <Link href="/master/profile" class="md-btn-secondary">
                                    {{ t('common.cancel') }}
                                </Link>
                                <button type="submit" class="md-btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing">{{ t('common.saving') }}...</span>
                                    <span v-else>{{ t('common.save') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Profile-specific styles */
.md-content-single {
    grid-template-columns: 1fr !important;
    max-width: 600px;
}

.md-profile-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.md-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #fff;
    border: 1px solid #E8E5E0;
    border-radius: 8px;
    color: #1B2B5A;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.md-back-btn:hover {
    background: #F5F2ED;
    border-color: #C8A951;
}

/* Photo Upload */
.md-photo-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 0;
}

.md-photo-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: #F5F2ED;
    border: 2px dashed #E8E5E0;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: border-color 0.2s ease;
}

.md-photo-preview:hover {
    border-color: #C8A951;
}

.md-photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.md-photo-placeholder {
    color: #C8C3BC;
}

.md-photo-overlay {
    position: absolute;
    inset: 0;
    background: rgba(27, 43, 90, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    color: #fff;
}

.md-photo-preview:hover .md-photo-overlay {
    opacity: 1;
}

.md-photo-hint {
    font-size: 13px;
    color: #8B8680;
    margin-top: 12px;
}

.hidden {
    display: none;
}

/* Form Styles */
.md-profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.md-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.md-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.md-form-label {
    font-size: 14px;
    font-weight: 600;
    color: #1B2B5A;
}

.md-form-input,
.md-form-select,
.md-form-textarea {
    width: 100%;
    padding: 12px 16px;
    background: #FAFAF8;
    border: 1px solid #E8E5E0;
    border-radius: 10px;
    font-size: 15px;
    color: #1B2B5A;
    transition: all 0.2s ease;
}

.md-form-input:focus,
.md-form-select:focus,
.md-form-textarea:focus {
    outline: none;
    border-color: #C8A951;
    box-shadow: 0 0 0 3px rgba(200, 169, 81, 0.1);
}

.md-form-input::placeholder,
.md-form-textarea::placeholder {
    color: #C8C3BC;
}

.md-form-disabled {
    background: #F5F2ED;
    color: #8B8680;
    cursor: not-allowed;
}

.md-form-error {
    border-color: #E53E3E;
}

.md-form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%238B8680' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 44px;
}

.md-form-textarea {
    resize: vertical;
    min-height: 80px;
}

.md-error-text {
    font-size: 13px;
    color: #E53E3E;
    margin: 0;
}

.md-hint-text {
    font-size: 12px;
    color: #8B8680;
    margin: 0;
}

/* Actions */
.md-form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 8px;
}

.md-btn-primary,
.md-btn-secondary {
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.md-btn-primary {
    background: linear-gradient(135deg, #C8A951, #B8963E);
    color: #fff;
    border: none;
}

.md-btn-primary:hover {
    background: linear-gradient(135deg, #B8963E, #A88530);
    transform: translateY(-1px);
}

.md-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.md-btn-secondary {
    background: #fff;
    color: #1B2B5A;
    border: 1px solid #E8E5E0;
}

.md-btn-secondary:hover {
    background: #F5F2ED;
    border-color: #C8A951;
}

/* Mobile */
@media (max-width: 768px) {
    .md-content-single {
        max-width: none;
    }

    .md-form-grid {
        grid-template-columns: 1fr;
    }

    .md-form-actions {
        flex-direction: column-reverse;
    }

    .md-btn-primary,
    .md-btn-secondary {
        width: 100%;
    }
}
</style>
