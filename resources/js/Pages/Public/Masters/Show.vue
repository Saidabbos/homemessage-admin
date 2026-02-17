<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import axios from 'axios'

const { t } = useI18n()

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const props = defineProps({
    master: {
        type: Object,
        required: true
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}

// ==================== QUICK BOOK MODAL ====================
const showQuickBook = ref(false)
const bookingStep = ref(1) // 1: service/date, 2: confirm
const selectedService = ref(null)
const selectedDuration = ref(null)
const selectedDate = ref('')
const selectedSlot = ref(null)
const guestCount = ref(1)
const address = ref('')
const notes = ref('')
const loadingSlots = ref(false)
const slots = ref([])
const submitting = ref(false)
const bookingError = ref('')

// Get available dates (7 days, starting from 6hr lead time)
const availableDates = computed(() => {
    const dates = []
    const now = new Date()
    
    // Add 6 hours lead time
    const minTime = new Date(now.getTime() + 6 * 60 * 60 * 1000)
    
    // Start date: today if there's still time, otherwise tomorrow
    const startDate = new Date(minTime)
    startDate.setHours(0, 0, 0, 0)
    
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    
    for (let i = 0; i < 7; i++) {
        const d = new Date(startDate)
        d.setDate(d.getDate() + i)
        
        const isToday = d.getTime() === today.getTime()
        
        dates.push({
            value: d.toISOString().split('T')[0],
            label: d.toLocaleDateString('uz-UZ', { weekday: 'short', day: 'numeric', month: 'short' }),
            dayName: d.toLocaleDateString('uz-UZ', { weekday: 'short' }),
            dayNum: d.getDate(),
            isToday
        })
    }
    return dates
})

// Selected service durations
const serviceDurations = computed(() => {
    if (!selectedService.value) return []
    const service = props.master.service_types?.find(s => s.id === selectedService.value)
    return service?.durations || []
})

// Selected price
const selectedPrice = computed(() => {
    if (!selectedDuration.value) return 0
    const duration = serviceDurations.value.find(d => d.id === selectedDuration.value)
    return duration?.price || 0
})

// Selected duration minutes
const selectedDurationMinutes = computed(() => {
    if (!selectedDuration.value) return 0
    const duration = serviceDurations.value.find(d => d.id === selectedDuration.value)
    return duration?.duration || 0
})

// Watch for changes to fetch slots
watch([selectedDate, selectedDuration, guestCount], async () => {
    if (selectedDate.value && selectedDuration.value) {
        await fetchSlots()
    }
})

// Fetch available slots
async function fetchSlots() {
    if (!selectedDate.value || !selectedDurationMinutes.value) return
    
    loadingSlots.value = true
    selectedSlot.value = null
    
    try {
        const response = await axios.get(`/api/masters/${props.master.id}/slots`, {
            params: {
                date: selectedDate.value,
                duration: selectedDurationMinutes.value,
                people_count: 1
            }
        })
        // API returns { success, data: { slots: [...] } }
        const rawSlots = response.data.data?.slots || response.data.slots || []
        // Filter only available slots, keep full slot object for display
        slots.value = rawSlots.filter(s => s.available && !s.disabled)
    } catch (err) {
        console.error('Error fetching slots:', err)
        slots.value = []
    } finally {
        loadingSlots.value = false
    }
}

// Open modal
function openQuickBook() {
    if (!authUser.value) {
        // Redirect to login
        router.visit('/auth/login', { 
            data: { redirect: `/masters/${props.master.id}` }
        })
        return
    }
    
    // Reset form
    selectedService.value = null
    selectedDuration.value = null
    selectedDate.value = ''
    selectedSlot.value = null
    guestCount.value = 1
    address.value = ''
    notes.value = ''
    slots.value = []
    bookingError.value = ''
    bookingStep.value = 1
    showQuickBook.value = true
}

// Close modal
function closeQuickBook() {
    showQuickBook.value = false
}

// Select service
function selectService(serviceId) {
    selectedService.value = serviceId
    selectedDuration.value = null
    selectedSlot.value = null
}

// Select duration
function selectDuration(durationId) {
    selectedDuration.value = durationId
    selectedSlot.value = null
}

// Select date
function selectDate(date) {
    selectedDate.value = date
    selectedSlot.value = null
}

// Select slot
function selectSlot(slot) {
    selectedSlot.value = slot
}

// Can proceed to step 2
const canProceed = computed(() => {
    return selectedService.value && 
           selectedDuration.value && 
           selectedDate.value && 
           selectedSlot.value &&
           address.value.trim().length > 5
})

// Go to confirm step
function goToConfirm() {
    if (canProceed.value) {
        bookingStep.value = 2
    }
}

// Go back to step 1
function goBack() {
    bookingStep.value = 1
}

// Submit booking
async function submitBooking() {
    if (!canProceed.value || submitting.value) return
    
    submitting.value = true
    bookingError.value = ''
    
    try {
        const response = await axios.post('/api/v1/bookings', {
            master_id: props.master.id,
            service_type_id: selectedService.value,
            duration: selectedDurationMinutes.value,
            date: selectedDate.value,
            arrival_window_start: selectedSlot.value.start,
            arrival_window_end: selectedSlot.value.end,
            people_count: 1,
            address: address.value,
            contact_phone: page.props.auth?.user?.phone || '',
            contact_name: page.props.auth?.user?.name || '',
            comment: notes.value,
            pressure_level: 'medium'
        })
        
        // Redirect to payment page
        const data = response.data.data || response.data
        if (data.payment_url) {
            router.visit(data.payment_url)
        } else if (data.booking_group_id) {
            router.visit(`/booking/payment/${data.booking_group_id}`)
        } else {
            router.visit('/booking/success')
        }
    } catch (err) {
        bookingError.value = err.response?.data?.message || 'Xatolik yuz berdi. Qayta urinib ko\'ring.'
    } finally {
        submitting.value = false
    }
}

// Get service name
const selectedServiceName = computed(() => {
    const service = props.master.service_types?.find(s => s.id === selectedService.value)
    return service?.name || ''
})

// Get duration label
const selectedDurationLabel = computed(() => {
    const duration = serviceDurations.value.find(d => d.id === selectedDuration.value)
    return duration?.formatted_duration || ''
})
</script>

<template>
    <Head :title="master.full_name" />

    <div class="master-detail-page">
        <!-- Navigation -->
        <nav class="md-nav">
            <Link href="/" class="md-nav-logo">
                <svg class="md-nav-logo-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <span class="md-nav-logo-text">HOMEMASSAGE</span>
            </Link>

            <div class="md-nav-links">
                <Link href="/" class="md-nav-link">{{ t('common.home') }}</Link>
                <a href="/#services" class="md-nav-link">{{ t('landing.nav.services') }}</a>
                <Link href="/masters" class="md-nav-link active">{{ t('landing.nav.masters') }}</Link>
                <a href="/#testimonials" class="md-nav-link">{{ t('landing.nav.testimonials') }}</a>
                <a href="/#contact" class="md-nav-link">{{ t('landing.contact.badge') }}</a>
            </div>

            <div class="md-nav-right">
                <button @click="openQuickBook" class="md-nav-cta">
                    {{ t('landing.nav.bookNow') }}
                </button>

                <!-- Auth: logged in -->
                <div v-if="authUser" class="md-nav-user">
                    <Link href="/customer/dashboard" class="md-nav-user-link">
                        <span class="md-nav-avatar">{{ authUser.avatar }}</span>
                        <span class="md-nav-username">{{ authUser.name }}</span>
                    </Link>
                </div>

                <!-- Auth: not logged in -->
                <Link v-else href="/auth/login" class="md-nav-login">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span>{{ t('public.master.login') }}</span>
                </Link>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="md-hero">
            <div class="md-hero-photo">
                <img
                    :src="master.photo_url || '/images/master-placeholder.svg'"
                    :alt="master.full_name"
                />
            </div>

            <div class="md-hero-info">
                <!-- Breadcrumb -->
                <div class="md-breadcrumb">
                    <Link href="/" class="md-bc-link">{{ t('common.home') }}</Link>
                    <span class="md-bc-sep">/</span>
                    <Link href="/masters" class="md-bc-link">{{ t('landing.nav.masters') }}</Link>
                    <span class="md-bc-sep">/</span>
                    <span class="md-bc-current">{{ master.full_name }}</span>
                </div>

                <!-- Name Block -->
                <div class="md-name-block">
                    <h1 class="md-master-name">{{ master.full_name }}</h1>
                    <p v-if="master.service_types?.length" class="md-master-role">
                        {{ master.service_types.map(st => st.name).join(' & ') }}
                    </p>
                </div>

                <!-- Short Bio -->
                <p v-if="master.bio" class="md-master-desc">{{ master.bio }}</p>

                <!-- Stats Row -->
                <div class="md-stats-row">
                    <div v-if="master.experience_years" class="md-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                        <div class="md-stat-text">
                            <span class="md-stat-val">{{ master.experience_years }} {{ t('public.masters.years') }}</span>
                            <span class="md-stat-label">{{ t('public.master.yearsExp') }}</span>
                        </div>
                    </div>
                    <div class="md-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <div class="md-stat-text">
                            <span class="md-stat-val">5.0</span>
                            <span class="md-stat-label">{{ t('public.master.rating') }}</span>
                        </div>
                    </div>
                    <div class="md-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <div class="md-stat-text">
                            <span class="md-stat-val">{{ master.completed_orders || '500+' }}</span>
                            <span class="md-stat-label">{{ t('public.master.clients') }}</span>
                        </div>
                    </div>
                </div>

                <!-- CTA Row -->
                <div class="md-cta-row">
                    <button @click="openQuickBook" class="md-cta-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('landing.nav.bookNow') }}</span>
                    </button>
                    <a href="tel:+998901234567" class="md-cta-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>{{ t('public.master.contact') }}</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section v-if="master.service_types?.length" class="md-services">
            <div class="md-section-header">
                <div class="md-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
                    <span>{{ t('public.master.services') }}</span>
                </div>
                <h2 class="md-section-title">{{ t('public.master.servicesOffered') }}</h2>
                <p class="md-section-subtitle">{{ master.full_name }} {{ t('public.master.servicesBy') }}</p>
            </div>

            <div class="md-services-grid">
                <div
                    v-for="(service, index) in master.service_types"
                    :key="service.id"
                    class="md-service-card"
                >
                    <div class="md-service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <template v-if="index % 3 === 0">
                                <path d="M18 11V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2"/><path d="M14 10V4a2 2 0 0 0-2-2a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                            </template>
                            <template v-else-if="index % 3 === 1">
                                <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/><circle cx="12" cy="12" r="3"/>
                            </template>
                            <template v-else>
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>
                            </template>
                        </svg>
                    </div>
                    <div class="md-service-info">
                        <h3 class="md-service-name">{{ service.name }}</h3>
                        <p v-if="service.description" class="md-service-desc">{{ service.description }}</p>
                    </div>
                    <!-- Durations with prices -->
                    <div class="md-service-durations">
                        <div
                            v-for="dur in service.durations"
                            :key="dur.id"
                            class="md-service-duration-row"
                        >
                            <div class="md-service-time">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>{{ dur.formatted_duration }}</span>
                            </div>
                            <span class="md-service-price">{{ formatPrice(dur.price) }} {{ t('common.sum') }}</span>
                        </div>
                    </div>
                    
                    <!-- Book this service button -->
                    <button @click="openQuickBook(); selectService(service.id)" class="md-service-book-btn">
                        Buyurtma berish
                    </button>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section v-if="master.bio" class="md-about">
            <div class="md-about-info">
                <div class="md-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ t('public.master.biography') }}</span>
                </div>
                <h2 class="md-about-title">{{ t('public.master.aboutTitle') }}</h2>
                <p class="md-about-desc">{{ master.bio }}</p>
            </div>

            <div class="md-about-sidebar">
                <!-- Skills / Service Types -->
                <div v-if="master.service_types?.length" class="md-sidebar-card">
                    <h3 class="md-sidebar-card-title">{{ t('public.master.specialization') }}</h3>
                    <div class="md-skill-tags">
                        <span
                            v-for="st in master.service_types"
                            :key="st.id"
                            class="md-skill-tag"
                        >{{ st.name }}</span>
                    </div>
                </div>

                <!-- Oils -->
                <div v-if="master.oils?.length" class="md-sidebar-card">
                    <h3 class="md-sidebar-card-title">{{ t('public.master.oils') }}</h3>
                    <div class="md-skill-tags">
                        <span
                            v-for="oil in master.oils"
                            :key="oil.id"
                            class="md-skill-tag"
                        >{{ oil.name }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="md-cta-section">
            <div class="md-cta-card">
                <h2 class="md-cta-title">{{ master.full_name }} {{ t('public.master.ctaBookWith') }}</h2>
                <p class="md-cta-desc">{{ t('public.master.ctaDesc') }}</p>
                <div class="md-cta-buttons">
                    <button @click="openQuickBook" class="md-cta-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('public.master.bookNow') }}</span>
                    </button>
                    <Link href="/masters" class="md-cta-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                        <span>{{ t('public.master.backToMasters') }}</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="md-footer">
            <div class="md-footer-divider"></div>
            <div class="md-footer-main">
                <div class="md-footer-brand">
                    <div class="md-footer-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span>HOMEMASSAGE</span>
                    </div>
                    <p class="md-footer-brand-desc">{{ t('landing.footer.tagline') }}</p>
                </div>
                <div class="md-footer-col">
                    <h4>{{ t('landing.nav.services') }}</h4>
                    <a href="#" @click.prevent="openQuickBook">{{ t('landing.nav.bookNow') }}</a>
                </div>
                <div class="md-footer-col">
                    <h4>{{ t('landing.footer.company') }}</h4>
                    <Link href="/masters">{{ t('landing.nav.masters') }}</Link>
                </div>
            </div>
            <div class="md-footer-divider"></div>
            <div class="md-footer-bottom">
                <span>&copy; 2026 HomeMassage. {{ t('landing.footer.rights') }}</span>
            </div>
        </footer>

        <!-- Quick Book Modal -->
        <div v-if="showQuickBook" class="qb-overlay" @click.self="closeQuickBook">
            <div class="qb-modal">
                <!-- Header -->
                <div class="qb-header">
                    <div class="qb-header-info">
                        <img :src="master.photo_url || '/images/master-placeholder.svg'" :alt="master.full_name" class="qb-master-thumb" />
                        <div>
                            <h2 class="qb-title">{{ master.full_name }}</h2>
                            <p class="qb-subtitle">Tez buyurtma</p>
                        </div>
                    </div>
                    <button class="qb-close" @click="closeQuickBook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <!-- Step 1: Select Service, Date, Slot -->
                <div v-if="bookingStep === 1" class="qb-body">
                    <!-- Service Selection -->
                    <div class="qb-section">
                        <label class="qb-label">Xizmat turi</label>
                        <div class="qb-service-list">
                            <button 
                                v-for="service in master.service_types" 
                                :key="service.id"
                                class="qb-service-btn"
                                :class="{ active: selectedService === service.id }"
                                @click="selectService(service.id)"
                            >
                                {{ service.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Duration Selection -->
                    <div v-if="selectedService && serviceDurations.length" class="qb-section">
                        <label class="qb-label">Davomiylik</label>
                        <div class="qb-duration-list">
                            <button 
                                v-for="dur in serviceDurations" 
                                :key="dur.id"
                                class="qb-duration-btn"
                                :class="{ active: selectedDuration === dur.id }"
                                @click="selectDuration(dur.id)"
                            >
                                <span class="qb-dur-time">{{ dur.formatted_duration }}</span>
                                <span class="qb-dur-price">{{ formatPrice(dur.price) }} so'm</span>
                            </button>
                        </div>
                    </div>

                    <!-- Date Selection -->
                    <div v-if="selectedDuration" class="qb-section">
                        <label class="qb-label">Sana</label>
                        <div class="qb-date-scroll">
                            <button 
                                v-for="date in availableDates" 
                                :key="date.value"
                                class="qb-date-btn"
                                :class="{ active: selectedDate === date.value, today: date.isToday }"
                                @click="selectDate(date.value)"
                            >
                                <span class="qb-date-day">{{ date.dayName }}</span>
                                <span class="qb-date-num">{{ date.dayNum }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Slot Selection -->
                    <div v-if="selectedDate" class="qb-section">
                        <label class="qb-label">Vaqt</label>
                        <div v-if="loadingSlots" class="qb-loading">
                            <span>Yuklanmoqda...</span>
                        </div>
                        <div v-else-if="slots.length === 0" class="qb-empty">
                            Bu sanada bo'sh vaqt yo'q
                        </div>
                        <div v-else class="qb-slot-grid">
                            <button 
                                v-for="slot in slots" 
                                :key="slot.start"
                                class="qb-slot-btn"
                                :class="{ active: selectedSlot?.start === slot.start }"
                                @click="selectSlot(slot)"
                            >
                                {{ slot.start }} - {{ slot.end }}
                            </button>
                        </div>
                    </div>

                    <!-- Address -->
                    <div v-if="selectedSlot" class="qb-section">
                        <label class="qb-label">Manzil</label>
                        <textarea 
                            v-model="address" 
                            class="qb-textarea" 
                            placeholder="To'liq manzilingizni kiriting..."
                            rows="2"
                        ></textarea>
                    </div>

                    <!-- Notes (optional) -->
                    <div v-if="selectedSlot" class="qb-section">
                        <label class="qb-label">Qo'shimcha izoh (ixtiyoriy)</label>
                        <textarea 
                            v-model="notes" 
                            class="qb-textarea" 
                            placeholder="Maxsus talablar, allergiyalar va h.k."
                            rows="2"
                        ></textarea>
                    </div>
                </div>

                <!-- Step 2: Confirm -->
                <div v-else class="qb-body">
                    <div class="qb-summary">
                        <h3 class="qb-summary-title">Buyurtma tafsilotlari</h3>
                        
                        <div class="qb-summary-row">
                            <span class="qb-summary-label">Xizmat:</span>
                            <span class="qb-summary-value">{{ selectedServiceName }}</span>
                        </div>
                        <div class="qb-summary-row">
                            <span class="qb-summary-label">Davomiylik:</span>
                            <span class="qb-summary-value">{{ selectedDurationLabel }}</span>
                        </div>
                        <div class="qb-summary-row">
                            <span class="qb-summary-label">Sana:</span>
                            <span class="qb-summary-value">{{ selectedDate }}</span>
                        </div>
                        <div class="qb-summary-row">
                            <span class="qb-summary-label">Vaqt:</span>
                            <span class="qb-summary-value">{{ selectedSlot?.start }} - {{ selectedSlot?.end }}</span>
                        </div>
                        <div class="qb-summary-row">
                            <span class="qb-summary-label">Manzil:</span>
                            <span class="qb-summary-value">{{ address }}</span>
                        </div>
                        
                        <div class="qb-summary-divider"></div>
                        
                        <div class="qb-summary-row qb-summary-total">
                            <span class="qb-summary-label">Jami:</span>
                            <span class="qb-summary-value">{{ formatPrice(selectedPrice) }} so'm</span>
                        </div>
                    </div>

                    <p v-if="bookingError" class="qb-error">{{ bookingError }}</p>
                </div>

                <!-- Footer -->
                <div class="qb-footer">
                    <div v-if="selectedPrice" class="qb-price-display">
                        <span class="qb-price-label">Jami:</span>
                        <span class="qb-price-value">{{ formatPrice(selectedPrice) }} so'm</span>
                    </div>
                    
                    <div class="qb-actions">
                        <button v-if="bookingStep === 2" class="qb-btn-secondary" @click="goBack">
                            Orqaga
                        </button>
                        <button 
                            v-if="bookingStep === 1"
                            class="qb-btn-primary" 
                            :disabled="!canProceed"
                            @click="goToConfirm"
                        >
                            Davom etish
                        </button>
                        <button 
                            v-else
                            class="qb-btn-primary" 
                            :disabled="submitting"
                            @click="submitBooking"
                        >
                            {{ submitting ? 'Yuklanmoqda...' : 'Buyurtma berish' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Quick Book Modal Styles */
.qb-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.qb-modal {
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    background: white;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.qb-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #f0f0f0;
}

.qb-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.qb-master-thumb {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
}

.qb-title {
    font-size: 18px;
    font-weight: 600;
    color: #1B2B5A;
    margin: 0;
}

.qb-subtitle {
    font-size: 13px;
    color: #8B8680;
    margin: 0;
}

.qb-close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border: none;
    border-radius: 10px;
    color: #666;
    cursor: pointer;
}

.qb-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 24px;
}

.qb-section {
    margin-bottom: 20px;
}

.qb-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #1B2B5A;
    margin-bottom: 10px;
}

.qb-service-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.qb-service-btn {
    padding: 10px 16px;
    background: #f5f5f5;
    border: 2px solid transparent;
    border-radius: 10px;
    font-size: 14px;
    color: #1B2B5A;
    cursor: pointer;
    transition: all 0.2s;
}

.qb-service-btn:hover {
    background: #eee;
}

.qb-service-btn.active {
    background: #C8A95115;
    border-color: #C8A951;
    color: #B8963E;
}

.qb-duration-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.qb-duration-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
    background: #f5f5f5;
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.qb-duration-btn:hover {
    background: #eee;
}

.qb-duration-btn.active {
    background: #C8A95115;
    border-color: #C8A951;
}

.qb-dur-time {
    font-size: 14px;
    font-weight: 600;
    color: #1B2B5A;
}

.qb-dur-price {
    font-size: 12px;
    color: #C8A951;
    margin-top: 2px;
}

.qb-date-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
}

.qb-date-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 56px;
    padding: 10px 12px;
    background: #f5f5f5;
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.qb-date-btn:hover {
    background: #eee;
}

.qb-date-btn.active {
    background: #C8A95115;
    border-color: #C8A951;
}

.qb-date-btn.today .qb-date-day {
    color: #C8A951;
}

.qb-date-day {
    font-size: 11px;
    color: #8B8680;
    text-transform: uppercase;
}

.qb-date-num {
    font-size: 18px;
    font-weight: 600;
    color: #1B2B5A;
    margin-top: 2px;
}

.qb-loading,
.qb-empty {
    text-align: center;
    padding: 20px;
    color: #8B8680;
    font-size: 14px;
}

.qb-slot-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.qb-slot-btn {
    padding: 10px;
    background: #f5f5f5;
    border: 2px solid transparent;
    border-radius: 8px;
    font-size: 14px;
    color: #1B2B5A;
    cursor: pointer;
    transition: all 0.2s;
}

.qb-slot-btn:hover {
    background: #eee;
}

.qb-slot-btn.active {
    background: #C8A95115;
    border-color: #C8A951;
    color: #B8963E;
}

.qb-guest-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.qb-guest-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border: none;
    border-radius: 10px;
    font-size: 20px;
    color: #1B2B5A;
    cursor: pointer;
}

.qb-guest-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qb-guest-count {
    font-size: 20px;
    font-weight: 600;
    color: #1B2B5A;
    min-width: 40px;
    text-align: center;
}

.qb-textarea {
    width: 100%;
    padding: 12px;
    background: #f9f9f9;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    font-size: 14px;
    resize: none;
}

.qb-textarea:focus {
    outline: none;
    border-color: #C8A951;
}

/* Summary */
.qb-summary {
    background: #f9f9f9;
    border-radius: 16px;
    padding: 20px;
}

.qb-summary-title {
    font-size: 16px;
    font-weight: 600;
    color: #1B2B5A;
    margin: 0 0 16px;
}

.qb-summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.qb-summary-label {
    color: #8B8680;
    font-size: 14px;
}

.qb-summary-value {
    color: #1B2B5A;
    font-size: 14px;
    font-weight: 500;
}

.qb-summary-divider {
    height: 1px;
    background: #e5e5e5;
    margin: 12px 0;
}

.qb-summary-total .qb-summary-label,
.qb-summary-total .qb-summary-value {
    font-size: 16px;
    font-weight: 600;
    color: #1B2B5A;
}

.qb-error {
    color: #dc2626;
    font-size: 14px;
    text-align: center;
    margin-top: 16px;
}

/* Footer */
.qb-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-top: 1px solid #f0f0f0;
    background: white;
}

.qb-price-display {
    display: flex;
    flex-direction: column;
}

.qb-price-label {
    font-size: 12px;
    color: #8B8680;
}

.qb-price-value {
    font-size: 18px;
    font-weight: 700;
    color: #1B2B5A;
}

.qb-actions {
    display: flex;
    gap: 10px;
}

.qb-btn-primary {
    padding: 12px 24px;
    background: linear-gradient(135deg, #C8A951, #B8963E);
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    color: white;
    cursor: pointer;
}

.qb-btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qb-btn-secondary {
    padding: 12px 24px;
    background: #f5f5f5;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    color: #1B2B5A;
    cursor: pointer;
}

/* Service card book button */
.md-service-book-btn {
    margin-top: 16px;
    width: 100%;
    padding: 10px;
    background: linear-gradient(135deg, #C8A951, #B8963E);
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
}

.md-service-book-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(200, 169, 81, 0.3);
}

@media (max-width: 640px) {
    .qb-modal {
        max-height: 95vh;
        border-radius: 20px 20px 0 0;
        margin-top: auto;
    }
    
    .qb-slot-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>
