<script setup>
const props = defineProps({
    rating: Object,
    customer: Object,
    type: String,
});

const getCustomerInitials = () => {
    const name = props.customer?.name || 'Mijoz';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <div class="complete-page">
        <div class="complete-container">
            <!-- Success Icon -->
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="22,4 12,14.01 9,11.01" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h1 class="title">Rahmat!</h1>
            <p class="subtitle">Mijoz bahosi qabul qilindi</p>

            <!-- Rating Summary -->
            <div class="rating-summary">
                <div class="customer-info">
                    <div class="customer-avatar">
                        <div class="avatar-placeholder">{{ getCustomerInitials() }}</div>
                    </div>
                    <div class="customer-details">
                        <span class="customer-name">{{ customer?.name || 'Mijoz' }}</span>
                        <div class="rating-display">
                            <span class="rating-value">{{ rating.overall_rating }}</span>
                            <span class="rating-text">yulduz</span>
                        </div>
                    </div>
                </div>

                <!-- Stars -->
                <div class="stars-display">
                    <svg 
                        v-for="star in 5" 
                        :key="star" 
                        viewBox="0 0 24 24" 
                        fill="currentColor"
                        :class="{ filled: star <= rating.overall_rating }"
                    >
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>

                <p v-if="rating.feedback" class="feedback-text">"{{ rating.feedback }}"</p>
            </div>

            <!-- CTA -->
            <a href="/" class="home-btn">Bosh sahifaga qaytish</a>
        </div>
    </div>
</template>

<style scoped>
.complete-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.complete-container {
    background: white;
    border-radius: 24px;
    padding: 40px 32px;
    width: 100%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.success-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    background: linear-gradient(135deg, #059669, #047857);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pop 0.5s ease;
}

@keyframes pop {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.success-icon svg {
    width: 40px;
    height: 40px;
    color: white;
}

.title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 8px;
}

.subtitle {
    color: #6b7280;
    font-size: 15px;
    margin: 0 0 32px;
}

.rating-summary {
    background: #f8fafc;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
}

.customer-info {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.customer-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #059669, #047857);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
}

.customer-details {
    text-align: left;
}

.customer-name {
    display: block;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.rating-display {
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.rating-value {
    font-size: 24px;
    font-weight: 700;
    color: #fbbf24;
}

.rating-text {
    color: #6b7280;
    font-size: 14px;
}

.stars-display {
    display: flex;
    justify-content: center;
    gap: 4px;
}

.stars-display svg {
    width: 28px;
    height: 28px;
    color: #e5e7eb;
}

.stars-display svg.filled {
    color: #fbbf24;
}

.feedback-text {
    margin: 16px 0 0;
    font-style: italic;
    color: #6b7280;
    font-size: 14px;
}

.home-btn {
    display: inline-block;
    padding: 14px 32px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    text-decoration: none;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.home-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(5, 150, 105, 0.4);
}
</style>
