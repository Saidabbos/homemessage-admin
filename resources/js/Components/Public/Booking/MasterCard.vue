<template>
  <div
    class="master-card"
    :class="{ 'master-card--selected': selected, 'master-card--all': isAll }"
    @click="$emit('click')"
  >
    <div class="master-card__avatar">
      <img v-if="!isAll && master.photo_url" :src="master.photo_url" :alt="master.name" />
      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path v-if="isAll" d="M23 21v-2a4 4 0 0 0-3-3.87" />
        <path v-if="isAll" d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
    </div>
    <div class="master-card__name">{{ master.first_name || master.name }}</div>
    <div class="master-card__indicator" v-if="selected">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12" />
      </svg>
    </div>
  </div>
</template>

<script setup>
defineProps({
  master: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
  isAll: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['click'])
</script>

<style scoped>
.master-card {
  flex-shrink: 0;
  width: 80px;
  text-align: center;
  cursor: pointer;
  position: relative;
}

.master-card__avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  overflow: hidden;
  margin: 0 auto 0.5rem;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid transparent;
  transition: all 0.2s ease;
}

.master-card--selected .master-card__avatar {
  border-color: #4299e1;
}

.master-card__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.master-card__avatar svg {
  width: 32px;
  height: 32px;
  color: #a0aec0;
}

.master-card--all .master-card__avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.master-card--all .master-card__avatar svg {
  color: white;
}

.master-card__name {
  font-size: 0.75rem;
  color: #4a5568;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.master-card--selected .master-card__name {
  color: #4299e1;
  font-weight: 600;
}

.master-card__indicator {
  position: absolute;
  top: 0;
  right: 8px;
  width: 20px;
  height: 20px;
  background: #4299e1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.master-card__indicator svg {
  width: 12px;
  height: 12px;
  color: white;
}
</style>
