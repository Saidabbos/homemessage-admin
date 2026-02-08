<template>
  <div class="success-page">
    <div class="success-card">
      <div class="success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
      </div>
      
      <h1 class="success-title">{{ $t('booking.success_title') }}</h1>
      <p class="success-message">{{ $t('booking.success_message') }}</p>
      
      <div class="order-number">
        <span class="order-number__label">{{ $t('booking.order_number') }}</span>
        <span class="order-number__value">{{ orderNumber }}</span>
      </div>
      
      <div class="next-steps">
        <h3>{{ $t('booking.next_steps') }}</h3>
        <ul>
          <li>{{ $t('booking.next_step_1') }}</li>
          <li>{{ $t('booking.next_step_2') }}</li>
          <li>{{ $t('booking.next_step_3') }}</li>
        </ul>
      </div>
      
      <a href="/" class="home-button">
        {{ $t('common.home') }}
      </a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  orderNumber: {
    type: String,
    default: '',
  },
})
</script>

<style scoped>
.success-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.success-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  text-align: center;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.success-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.success-icon svg {
  width: 40px;
  height: 40px;
  color: white;
}

.success-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.success-message {
  color: #718096;
  margin-bottom: 1.5rem;
}

.order-number {
  background: #f7fafc;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.order-number__label {
  display: block;
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 0.25rem;
}

.order-number__value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #4299e1;
}

.next-steps {
  text-align: left;
  background: #ebf8ff;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.next-steps h3 {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2b6cb0;
  margin-bottom: 0.5rem;
}

.next-steps ul {
  font-size: 0.875rem;
  color: #2c5282;
  padding-left: 1.25rem;
  margin: 0;
}

.next-steps li {
  margin-bottom: 0.25rem;
}

.home-button {
  display: inline-block;
  background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
  color: white;
  padding: 0.875rem 2rem;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}

.home-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
}
</style>
