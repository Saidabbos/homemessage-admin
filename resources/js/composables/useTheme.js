import { ref, watch } from 'vue';

const THEME_KEY = 'sabai-theme';

const themes = [
    { id: 'blush', name: 'Blush Serenity', class: 'theme-blush', colors: { gold: '#D0B06B', sage: '#E8C9C3', forest: '#1E1F24', ivory: '#FBFAF9', champagne: '#E6E7EA' } },
    { id: 'sage-champagne', name: 'Sage & Champagne', class: '', colors: { gold: '#C8A65E', sage: '#9BAF9B', forest: '#1F2D2A', ivory: '#FCFBF8', champagne: '#EADFCB' } },
    { id: 'sand', name: 'Sand Minimal', class: 'theme-sand', colors: { gold: '#B58B4F', sage: '#B9AA9A', forest: '#222428', ivory: '#FFFFFF', champagne: '#EFE6D8' } },
    { id: 'classic', name: 'Classic Gold', class: 'theme-classic', colors: { gold: '#C9A55C', sage: '#D4B76A', forest: '#1A1A2E', ivory: '#FAF8F5', champagne: '#F5F0E8' } },
    { id: 'azure', name: 'Azure Calm', class: 'theme-azure', colors: { gold: '#C7A15A', sage: '#F1E8DB', forest: '#121B2B', ivory: '#FAFBFC', champagne: '#DDEAF4' } },
    { id: 'mono', name: 'Monochrome Luxury', class: 'theme-mono', colors: { gold: '#BFA06A', sage: '#C8C3BA', forest: '#2A2B2F', ivory: '#FDFDFC', champagne: '#F0F1F3' } },
];

const currentTheme = ref(localStorage.getItem(THEME_KEY) || 'sage-champagne');

function applyTheme(themeId) {
    const theme = themes.find(t => t.id === themeId);
    if (!theme) return;

    // Remove all theme classes
    const html = document.documentElement;
    themes.forEach(t => {
        if (t.class) html.classList.remove(t.class);
    });

    // Add new theme class
    if (theme.class) {
        html.classList.add(theme.class);
    }

    currentTheme.value = themeId;
    localStorage.setItem(THEME_KEY, themeId);
}

// Apply saved theme on import
if (typeof window !== 'undefined') {
    applyTheme(currentTheme.value);
}

export function useTheme() {
    return {
        themes,
        currentTheme,
        applyTheme,
    };
}
