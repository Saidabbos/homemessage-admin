<template>
    <div class="theme-switcher">
        <label class="theme-switcher__label">{{ t('settings.theme') }}</label>
        <div class="theme-switcher__grid">
            <button
                v-for="theme in themes"
                :key="theme.id"
                class="theme-switcher__item"
                :class="{ 'theme-switcher__item--active': currentTheme === theme.id }"
                @click="applyTheme(theme.id)"
                :title="theme.name"
            >
                <div class="theme-switcher__preview">
                    <div class="theme-switcher__swatch" :style="{ background: theme.colors.ivory }">
                        <div class="theme-switcher__dot theme-switcher__dot--gold" :style="{ background: theme.colors.gold }"></div>
                        <div class="theme-switcher__dot theme-switcher__dot--sage" :style="{ background: theme.colors.sage }"></div>
                        <div class="theme-switcher__dot theme-switcher__dot--forest" :style="{ background: theme.colors.forest }"></div>
                        <div class="theme-switcher__bar" :style="{ background: theme.colors.champagne }"></div>
                    </div>
                </div>
                <span class="theme-switcher__name">{{ theme.name }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { useTheme } from '@/composables/useTheme.js';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { themes, currentTheme, applyTheme } = useTheme();
</script>

<style scoped>
.theme-switcher__label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: var(--c-forest);
    margin-bottom: 12px;
}

.theme-switcher__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.theme-switcher__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 10px;
    border: 2px solid var(--c-champagne);
    border-radius: 12px;
    background: var(--c-white);
    cursor: pointer;
    transition: all 0.2s ease;
}

.theme-switcher__item:hover {
    border-color: var(--c-gold-40);
}

.theme-switcher__item--active {
    border-color: var(--c-gold);
    box-shadow: 0 0 0 3px var(--c-gold-15);
}

.theme-switcher__preview {
    width: 100%;
    aspect-ratio: 16 / 10;
    border-radius: 8px;
    overflow: hidden;
}

.theme-switcher__swatch {
    width: 100%;
    height: 100%;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 8px;
    position: relative;
}

.theme-switcher__dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
}

.theme-switcher__bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 30%;
    border-radius: 0 0 6px 6px;
}

.theme-switcher__name {
    font-size: 11px;
    font-weight: 500;
    color: var(--c-forest);
    text-align: center;
    line-height: 1.2;
}

@media (max-width: 480px) {
    .theme-switcher__grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
