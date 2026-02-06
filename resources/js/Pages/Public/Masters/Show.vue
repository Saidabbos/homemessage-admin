<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref, watch, onMounted } from 'vue'
import DateSelector from '@/Components/Public/DateSelector.vue'
import axios from 'axios'

const { t, locale } = useI18n()

const props = defineProps({
    master: {
        type: Object,
        required: true
    }
})

const selectedDate = ref(null)
const slots = ref([])
const loading = ref(false)
const error = ref(null)

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}

const fetchSlots = async (date) => {
    if (!date) return
    
    loading.value = true
    error.value = null
    
    try {
        const response = await axios.get(`/api/v1/masters/${props.master.id}/slots`, {
            params: { date }
        })
        slots.value = response.data.data || []
    } catch (err) {
        console.error('Failed to fetch slots:', err)
        error.value = t('public.slots.error')
        slots.value = []
    } finally {
        loading.value = false
    }
}

watch(selectedDate, (newDate) => {
    if (newDate) {
        fetchSlots(newDate)
    }
})

const getSlotStatusClass = (status) => {
    const classes = {
        FREE: 'slot-free',
        PENDING: 'slot-pending',
        RESERVED: 'slot-reserved',
        BLOCKED: 'slot-blocked',
        UNAVAILABLE: 'slot-unavailable'
    }
    return classes[status] || 'slot-unavailable'
}

const getSlotStatusText = (status) => {
    const texts = {
        FREE: t('public.slots.status.free'),
        PENDING: t('public.slots.status.pending'),
        RESERVED: t('public.slots.status.reserved'),
        BLOCKED: t('public.slots.status.blocked'),
        UNAVAILABLE: t('public.slots.status.unavailable')
    }
    return texts[status] || status
}

const formatTime = (time) => {
    return time?.substring(0, 5) || time
}
</script>

<template>
    <Head :title="master.full_name" />
    
    <div class="master-detail-page">
        <!-- Header -->
        <header class="detail-header">
            <div class="header-content">
                <Link href="/masters" class="back-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </Link>
                <h1 class="page-title">{{ t('public.master.detail') }}</h1>
                <div class="header-spacer"></div>
            </div>
        </header>

        <!-- Master Info Section -->
        <section class="master-hero">
            <div class="master-photo-large">
                <img 
                    :src="master.photo_url || '/images/master-placeholder.svg'" 
                    :alt="master.full_name"
                />
            </div>
            
            <div class="master-details">
                <h2 class="master-name">{{ master.full_name }}</h2>
                
                <div class="master-stats">
                    <div v-if="master.experience_years" class="stat-item">
                        <span class="stat-value">{{ master.experience_years }}</span>
                        <span class="stat-label">{{ t('public.master.yearsExp') }}</span>
                    </div>
                </div>

                <p v-if="master.bio" class="master-bio">
                    {{ master.bio }}
                </p>
            </div>
        </section>

        <!-- Services Section -->
        <section v-if="master.service_types?.length" class="services-section">
            <h3 class="section-title">{{ t('public.master.services') }}</h3>
            <div class="services-list">
                <div 
                    v-for="service in master.service_types" 
                    :key="service.id"
                    class="service-item"
                >
                    <div class="service-info">
                        <span class="service-name">{{ service.name }}</span>
                        <span v-if="service.duration" class="service-duration">
                            {{ service.duration }} {{ t('common.min') }}
                        </span>
                    </div>
                    <span class="service-price">
                        {{ formatPrice(service.price) }} {{ t('common.sum') }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Oils Section -->
        <section v-if="master.oils?.length" class="oils-section">
            <h3 class="section-title">{{ t('public.master.oils') }}</h3>
            <div class="oils-list">
                <span 
                    v-for="oil in master.oils" 
                    :key="oil.id"
                    class="oil-tag"
                >
                    🧴 {{ oil.name }}
                </span>
            </div>
        </section>

        <!-- Date Selection -->
        <section class="date-section">
            <h3 class="section-title">{{ t('public.slots.selectDate') }}</h3>
            <DateSelector v-model="selectedDate" :days="7" />
        </section>

        <!-- Slots Grid -->
        <section class="slots-section">
            <h3 class="section-title">{{ t('public.slots.available') }}</h3>
            
            <!-- Loading State -->
            <div v-if="loading" class="slots-loading">
                <div class="loading-spinner"></div>
                <p>{{ t('common.loading') }}</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="slots-error">
                <p>{{ error }}</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="slots.length === 0 && selectedDate" class="slots-empty">
                <p>{{ t('public.slots.empty') }}</p>
            </div>

            <!-- Slots Grid -->
            <div v-else class="slots-grid">
                <button
                    v-for="slot in slots"
                    :key="slot.id"
                    class="slot-card"
                    :class="getSlotStatusClass(slot.status)"
                    :disabled="slot.status !== 'FREE'"
                >
                    <span class="slot-time">{{ formatTime(slot.start_time) }}</span>
                    <span class="slot-status">{{ getSlotStatusText(slot.status) }}</span>
                </button>
            </div>
        </section>

        <!-- Bottom Safe Area -->
        <div class="bottom-safe-area"></div>
    </div>
</template>
