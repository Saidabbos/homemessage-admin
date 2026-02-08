<template>
  <div
    class="service-card"
    :class="{ 'service-card--selected': selected }"
    @click="$emit('click')"
  >
    <div class="service-card__image">
      <img :src="service.image_url" :alt="service.name" />
    </div>
    <div class="service-card__content">
      <h3 class="service-card__name">{{ service.name }}</h3>
      <div class="service-card__meta">
        <span class="service-card__duration">{{ service.duration }} min</span>
        <span class="service-card__price">{{ service.price_formatted }}</span>
      </div>
    </div>
    <div class="service-card__check" v-if="selected">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12" />
      </svg>
    </div>
  </div>
</template>

<script setup>
defineProps({
  service: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['click'])
</script>

<style scoped>
.service-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  border: 2px solid transparent;
}

.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.service-card--selected {
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
}

.service-card__image {
  width: 100%;
  height: 100px;
  overflow: hidden;
}

.service-card__image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.service-card__content {
  padding: 0.75rem;
}

.service-card__name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 0.25rem;
  line-height: 1.3;
}

.service-card__meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
}

.service-card__duration {
  color: #718096;
}

.service-card__price {
  font-weight: 600;
  color: #48bb78;
}

.service-card__check {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  background: #4299e1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.service-card__check svg {
  width: 14px;
  height: 14px;
}
</style>
