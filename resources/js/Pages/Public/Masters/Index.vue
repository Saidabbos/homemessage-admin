<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref, computed } from 'vue'

const { t, locale } = useI18n()

const props = defineProps({
    masters: {
        type: Array,
        default: () => []
    },
    serviceTypes: {
        type: Array,
        default: () => []
    }
})

const selectedServiceType = ref(null)

const filteredMasters = computed(() => {
    if (!selectedServiceType.value) {
        return props.masters
    }
    return props.masters.filter(master => 
        master.service_types.some(st => st.id === selectedServiceType.value)
    )
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}
</script>

<template>
    <Head :title="t('public.masters.title')" />
    
    <div class="masters-page">
        <!-- Header -->
        <header class="masters-header">
            <div class="header-content">
                <Link href="/" class="back-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </Link>
                <h1 class="page-title">{{ t('public.masters.title') }}</h1>
                <div class="header-spacer"></div>
            </div>
        </header>

        <!-- Filter -->
        <div class="filter-section">
            <div class="filter-chips">
                <button 
                    class="filter-chip"
                    :class="{ active: !selectedServiceType }"
                    @click="selectedServiceType = null"
                >
                    {{ t('common.all') }}
                </button>
                <button 
                    v-for="st in serviceTypes" 
                    :key="st.id"
                    class="filter-chip"
                    :class="{ active: selectedServiceType === st.id }"
                    @click="selectedServiceType = st.id"
                >
                    {{ st.name }}
                </button>
            </div>
        </div>

        <!-- Masters Grid -->
        <main class="masters-content">
            <div v-if="filteredMasters.length === 0" class="empty-state">
                <div class="empty-icon">👤</div>
                <p>{{ t('public.masters.empty') }}</p>
            </div>

            <div v-else class="masters-grid">
                <Link
                    v-for="master in filteredMasters"
                    :key="master.id"
                    :href="`/masters/${master.id}`"
                    class="master-card"
                >
                    <div class="master-photo-wrapper">
                        <img 
                            :src="master.photo_url || '/images/master-placeholder.svg'" 
                            :alt="master.full_name"
                            class="master-photo"
                        />
                    </div>
                    
                    <div class="master-info">
                        <h3 class="master-name">{{ master.full_name }}</h3>
                        
                        <p v-if="master.bio" class="master-bio">
                            {{ master.bio }}
                        </p>
                        
                        <div class="master-meta">
                            <span v-if="master.experience_years" class="meta-item">
                                <span class="meta-icon">⭐</span>
                                {{ master.experience_years }} {{ t('public.masters.years') }}
                            </span>
                            <span v-if="master.completed_orders" class="meta-item">
                                <span class="meta-icon">✓</span>
                                {{ master.completed_orders }} {{ t('public.masters.orders') }}
                            </span>
                        </div>

                        <div v-if="master.service_types.length" class="service-tags">
                            <span 
                                v-for="st in master.service_types.slice(0, 2)" 
                                :key="st.id"
                                class="service-tag"
                            >
                                {{ st.name }}
                            </span>
                            <span v-if="master.service_types.length > 2" class="service-tag more">
                                +{{ master.service_types.length - 2 }}
                            </span>
                        </div>
                    </div>

                    <div class="card-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </div>
                </Link>
            </div>
        </main>
    </div>
</template>
