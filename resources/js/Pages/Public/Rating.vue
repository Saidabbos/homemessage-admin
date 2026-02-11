<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    rating: Object,
    order: Object,
    master: Object,
});

const form = useForm({
    overall_rating: 0,
    punctuality_rating: 0,
    professionalism_rating: 0,
    cleanliness_rating: 0,
    feedback: '',
});

const hoveredRating = ref({ overall: 0, punctuality: 0, professionalism: 0, cleanliness: 0 });

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
</script>

<template>
    <div class="rating-page">
        <div class="rating-container">
            <!-- Header -->
            <div class="rating-header">
                <div class="master-avatar">
                    <img v-if="master.photo" :src="master.photo" :alt="master.full_name" />
                    <div v-else class="avatar-placeholder">
                        {{ master.first_name?.[0] }}{{ master.last_name?.[0] }}
                    </div>
                </div>
                <h1 class="master-name">{{ master.full_name }}</h1>
                <p class="order-info">{{ order.service_type?.name }} • {{ formatDate(order.booking_date) }}</p>
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

                <!-- Additional Ratings -->
                <div class="additional-ratings">
                    <div class="rating-section">
                        <label class="rating-label-sm">Vaqtida kelish</label>
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

                    <div class="rating-section">
                        <label class="rating-label-sm">Professionallik</label>
                        <div class="stars-container small">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="star-btn small"
                                :class="{ filled: star <= (hoveredRating.professionalism || form.professionalism_rating) }"
                                @mouseenter="hoveredRating.professionalism = star"
                                @mouseleave="hoveredRating.professionalism = 0"
                                @click="setRating('professionalism', star)"
                            >
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="rating-section">
                        <label class="rating-label-sm">Tozalik</label>
                        <div class="stars-container small">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="star-btn small"
                                :class="{ filled: star <= (hoveredRating.cleanliness || form.cleanliness_rating) }"
                                @mouseenter="hoveredRating.cleanliness = star"
                                @mouseleave="hoveredRating.cleanliness = 0"
                                @click="setRating('cleanliness', star)"
                            >
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Feedback -->
                <div class="feedback-section">
                    <label class="rating-label">Izoh (ixtiyoriy)</label>
                    <textarea
                        v-model="form.feedback"
                        class="feedback-input"
                        rows="4"
                        placeholder="Xizmat haqida fikringizni yozing..."
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.master-avatar {
    width: 80px;
    height: 80px;
    margin: 0 auto 16px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #667eea;
}

.master-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 600;
}

.master-name {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 8px;
}

.order-info {
    color: #6b7280;
    font-size: 14px;
    margin: 0;
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

.star-btn.required {
    animation: shake 0.5s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
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

.additional-ratings {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
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
    border-color: #667eea;
}

.submit-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 480px) {
    .rating-container {
        padding: 24px;
    }
    
    .additional-ratings {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .star-btn {
        width: 40px;
        height: 40px;
    }
}
</style>
