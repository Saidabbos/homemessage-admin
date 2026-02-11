<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    rating: Object,
    order: Object,
    customer: Object,
    type: String,
});

const form = useForm({
    overall_rating: 0,
    punctuality_rating: 0,
    feedback: '',
});

const hoveredRating = ref({ overall: 0, punctuality: 0 });

const setRating = (field, value) => {
    form[field + '_rating'] = value;
};

const submit = () => {
    form.post(route('rating.store', props.rating.token));
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const getCustomerInitials = () => {
    const name = props.customer?.name || 'Mijoz';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <div class="rating-page">
        <div class="rating-container">
            <!-- Header -->
            <div class="rating-header">
                <div class="customer-avatar">
                    <div class="avatar-placeholder">{{ getCustomerInitials() }}</div>
                </div>
                <h1 class="customer-name">{{ customer?.name || 'Mijoz' }}</h1>
                <p class="order-info">{{ order.service_type?.name }} • {{ formatDate(order.booking_date) }}</p>
                <span class="badge">Mijozni baholash</span>
            </div>

            <!-- Rating Form -->
            <form @submit.prevent="submit" class="rating-form">
                <!-- Overall Rating -->
                <div class="rating-section main-rating">
                    <label class="rating-label">Umumiy baho</label>
                    <div class="stars-container">
                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            class="star-btn"
                            :class="{ 
                                filled: star <= (hoveredRating.overall || form.overall_rating),
                                required: form.errors.overall_rating
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
                    <span v-if="form.errors.overall_rating" class="error-text">Bahoni tanlang</span>
                </div>

                <!-- Punctuality Rating -->
                <div class="rating-section">
                    <label class="rating-label-sm">Vaqtida tayyorligi</label>
                    <div class="stars-container small">
                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            class="star-btn small"
                            :class="{ filled: star <= (hoveredRating.punctuality || form.punctuality_rating) }"
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

                <!-- Feedback -->
                <div class="feedback-section">
                    <label class="rating-label">Izoh (ixtiyoriy)</label>
                    <textarea
                        v-model="form.feedback"
                        class="feedback-input"
                        rows="3"
                        placeholder="Mijoz haqida izoh..."
                    ></textarea>
                </div>

                <!-- Submit -->
                <button 
                    type="submit" 
                    class="submit-btn"
                    :disabled="form.processing || form.overall_rating === 0"
                >
                    {{ form.processing ? 'Yuborilmoqda...' : 'Baholash' }}
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.rating-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rating-container {
    background: white;
    border-radius: 24px;
    padding: 32px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.rating-header {
    text-align: center;
    margin-bottom: 32px;
}

.customer-avatar {
    width: 80px;
    height: 80px;
    margin: 0 auto 16px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #059669;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #059669, #047857);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 600;
}

.customer-name {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 8px;
}

.order-info {
    color: #6b7280;
    font-size: 14px;
    margin: 0 0 12px;
}

.badge {
    display: inline-block;
    padding: 6px 16px;
    background: linear-gradient(135deg, #059669, #047857);
    color: white;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
}

.rating-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.rating-section {
    text-align: center;
}

.main-rating {
    padding: 20px;
    background: #f8fafc;
    border-radius: 16px;
}

.rating-label {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 12px;
}

.rating-label-sm {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 8px;
}

.stars-container {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.stars-container.small {
    gap: 4px;
}

.star-btn {
    width: 48px;
    height: 48px;
    padding: 0;
    background: none;
    border: none;
    cursor: pointer;
    color: #e5e7eb;
    transition: all 0.2s ease;
}

.star-btn.small {
    width: 32px;
    height: 32px;
}

.star-btn:hover,
.star-btn.filled {
    color: #fbbf24;
    transform: scale(1.1);
}

.star-btn svg {
    width: 100%;
    height: 100%;
}

.error-text {
    display: block;
    color: #ef4444;
    font-size: 13px;
    margin-top: 8px;
}

.feedback-section {
    text-align: left;
}

.feedback-input {
    width: 100%;
    padding: 14px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 15px;
    resize: none;
    transition: border-color 0.2s;
}

.feedback-input:focus {
    outline: none;
    border-color: #059669;
}

.submit-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(5, 150, 105, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
