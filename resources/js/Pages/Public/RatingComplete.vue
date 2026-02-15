<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
    rating: Object,
    master: Object,
    order: Object,
    type: String,
})

const getRatingText = (rating) => {
    if (rating >= 5) return t('rating.excellent')
    if (rating >= 4) return t('rating.good')
    if (rating >= 3) return t('rating.average')
    if (rating >= 2) return t('rating.poor')
    return t('rating.veryPoor')
}

const isAuthenticated = !!page.props?.auth?.user
</script>

<template>
    <div class="rc-page">
        <div class="rc-container">
            <!-- Success Header -->
            <div class="rc-header">
                <div class="rc-success-icon rc-success-icon-customer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20,6 9,17 4,12"/>
                    </svg>
                </div>
                <h1 class="rc-title">{{ t('rating.thankYou') }}</h1>
                <p class="rc-subtitle">{{ t('rating.ratingAccepted') }}</p>
            </div>

            <!-- Rating Card -->
            <div class="rc-card">
                <!-- Order Badge -->
                <div v-if="order" class="rc-order-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ order.service_name }} &middot; {{ order.booking_date }}
                </div>

                <!-- Master Info -->
                <div class="rc-person">
                    <div class="rc-avatar">
                        <img v-if="master.photo" :src="master.photo" :alt="master.full_name" />
                        <div v-else class="rc-avatar-placeholder rc-avatar-placeholder-customer">
                            {{ master.first_name?.[0] }}{{ master.last_name?.[0] }}
                        </div>
                    </div>
                    <div class="rc-person-info">
                        <span class="rc-person-name">{{ master.full_name }}</span>
                        <span class="rc-person-role">{{ t('rating.massageTherapist') }}</span>
                    </div>
                </div>

                <!-- Overall Rating -->
                <div class="rc-overall">
                    <span class="rc-overall-value">{{ rating.overall_rating }}</span>
                    <div class="rc-overall-text">{{ getRatingText(rating.overall_rating) }}</div>
                    <div class="rc-stars">
                        <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="s <= rating.overall_rating ? '#C8A951' : '#E8E5E0'" :stroke="s <= rating.overall_rating ? '#C8A951' : '#E8E5E0'" stroke-width="1" :class="s <= rating.overall_rating ? 'rc-star-filled' : 'rc-star-empty'">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>

                <!-- Sub Ratings -->
                <div v-if="rating.punctuality_rating || rating.professionalism_rating || rating.cleanliness_rating" class="rc-sub-ratings">
                    <div v-if="rating.punctuality_rating" class="rc-sub-item">
                        <span class="rc-sub-label">{{ t('rating.punctuality') }}</span>
                        <span class="rc-sub-value">{{ rating.punctuality_rating }}/5</span>
                        <div class="rc-sub-stars">
                            <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="s <= rating.punctuality_rating ? '#C8A951' : '#E8E5E0'" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                    </div>
                    <div v-if="rating.professionalism_rating" class="rc-sub-item">
                        <span class="rc-sub-label">{{ t('rating.professionalism') }}</span>
                        <span class="rc-sub-value">{{ rating.professionalism_rating }}/5</span>
                        <div class="rc-sub-stars">
                            <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="s <= rating.professionalism_rating ? '#C8A951' : '#E8E5E0'" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                    </div>
                    <div v-if="rating.cleanliness_rating" class="rc-sub-item">
                        <span class="rc-sub-label">{{ t('rating.cleanliness') }}</span>
                        <span class="rc-sub-value">{{ rating.cleanliness_rating }}/5</span>
                        <div class="rc-sub-stars">
                            <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="s <= rating.cleanliness_rating ? '#C8A951' : '#E8E5E0'" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Feedback -->
                <div v-if="rating.feedback" class="rc-feedback">
                    <div class="rc-feedback-label">{{ t('rating.yourFeedback') }}</div>
                    <p class="rc-feedback-text">"{{ rating.feedback }}"</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="rc-actions">
                <Link href="/customer/dashboard" class="rc-btn rc-btn-primary-customer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    {{ t('rating.goToDashboard') }}
                </Link>
                <Link href="/" class="rc-btn rc-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    {{ t('rating.goHome') }}
                </Link>
            </div>
        </div>
    </div>
</template>
