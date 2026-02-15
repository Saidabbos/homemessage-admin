<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const tr = (field) => {
    if (typeof field === 'string') return field
    if (field && typeof field === 'object') {
        return field[locale.value] || field.uz || field.ru || field.en || Object.values(field)[0] || ''
    }
    return ''
}

const props = defineProps({
    rating: Object,
    order: Object,
    customer: Object,
    type: String,
})

const form = useForm({
    overall_rating: 0,
    punctuality_rating: 0,
    feedback: '',
})

const hoveredRating = ref({ overall: 0, punctuality: 0 })

const setRating = (field, value) => {
    form[field + '_rating'] = value
}

const submit = () => {
    form.post(route('rating.store', props.rating.token))
}

const localeMap = { uz: 'ru-RU', ru: 'ru-RU', en: 'en-US' }

const formatDate = (date) => {
    const loc = localeMap[locale.value] || 'ru-RU'
    return new Date(date).toLocaleDateString(loc, {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const getCustomerInitials = () => {
    const name = props.customer?.name || 'Mijoz'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}
</script>

<template>
    <div class="rf-page">
        <div class="rf-container">
            <!-- Header -->
            <div class="rf-header">
                <div class="rf-avatar rf-avatar-master">
                    <div class="rf-avatar-placeholder">{{ getCustomerInitials() }}</div>
                </div>
                <h1 class="rf-name">{{ customer?.name || 'Mijoz' }}</h1>
                <p class="rf-order-info">{{ tr(order.service_type?.name) }} &middot; {{ formatDate(order.booking_date) }}</p>
                <span class="rf-badge rf-badge-master">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    {{ t('rating.rateClient') }}
                </span>
            </div>

            <!-- Rating Form Card -->
            <div class="rf-card">
                <form @submit.prevent="submit">
                    <!-- Overall Rating -->
                    <div class="rf-main-rating">
                        <label class="rf-label">{{ t('rating.overallRating') }}</label>
                        <div class="rf-stars">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="rf-star"
                                :class="{
                                    'rf-star-filled': star <= (hoveredRating.overall || form.overall_rating),
                                    'rf-star-error': form.errors.overall_rating
                                }"
                                @mouseenter="hoveredRating.overall = star"
                                @mouseleave="hoveredRating.overall = 0"
                                @click="setRating('overall', star)"
                            >
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                        </div>
                        <span v-if="form.errors.overall_rating" class="rf-error">{{ t('rating.selectRating') }}</span>
                    </div>

                    <!-- Punctuality Sub Rating -->
                    <div class="rf-sub-ratings rf-sub-ratings-single">
                        <div class="rf-section">
                            <label class="rf-label-sm">{{ t('rating.customerReadiness') }}</label>
                            <div class="rf-stars rf-stars-sm">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    class="rf-star rf-star-sm"
                                    :class="{ 'rf-star-filled': star <= (hoveredRating.punctuality || form.punctuality_rating) }"
                                    @mouseenter="hoveredRating.punctuality = star"
                                    @mouseleave="hoveredRating.punctuality = 0"
                                    @click="setRating('punctuality', star)"
                                >
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback -->
                    <div class="rf-feedback-section">
                        <label class="rf-label">{{ t('rating.feedbackOptional') }}</label>
                        <textarea
                            v-model="form.feedback"
                            class="rf-textarea"
                            rows="3"
                            :placeholder="t('rating.feedbackPlaceholderClient')"
                        ></textarea>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="rf-submit rf-submit-master"
                        :disabled="form.processing || form.overall_rating === 0"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        {{ form.processing ? t('rating.submitting') : t('rating.submit') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
