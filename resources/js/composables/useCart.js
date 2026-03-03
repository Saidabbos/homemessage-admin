import { ref, computed, watch } from 'vue';

const CART_KEY_PREFIX = 'hm_cart';
const CART_EXPIRY_PREFIX = 'hm_cart_expiry';
const CART_USER_KEY = 'hm_cart_user';
const CART_TTL = 24 * 60 * 60 * 1000; // 24 hours

// Get current user identifier (telegram_id or user_id from page props)
const getCurrentUserId = () => {
    // Try Telegram user
    const tgUser = window.Telegram?.WebApp?.initDataUnsafe?.user;
    if (tgUser?.id) return `tg_${tgUser.id}`;
    // Try Inertia page props
    const page = window.__page || document.querySelector('[data-page]');
    if (page) {
        try {
            const props = typeof page === 'object' ? page.props : JSON.parse(page.dataset.page).props;
            if (props?.auth?.user?.id) return `u_${props.auth.user.id}`;
        } catch {}
    }
    return 'guest';
};

const getCartKey = () => `${CART_KEY_PREFIX}_${getCurrentUserId()}`;
const getExpiryKey = () => `${CART_EXPIRY_PREFIX}_${getCurrentUserId()}`;

// Shared reactive state
const cart = ref([]);
const isLoaded = ref(false);
let loadedForUser = null;

// Clear cart data for a different user if account switched
const checkUserSwitch = () => {
    const currentUser = getCurrentUserId();
    const storedUser = localStorage.getItem(CART_USER_KEY);
    if (storedUser && storedUser !== currentUser) {
        // Account switched — reset loaded state so cart reloads for new user
        isLoaded.value = false;
        cart.value = [];
    }
    localStorage.setItem(CART_USER_KEY, currentUser);
};

// Load cart from localStorage
const loadCart = () => {
    checkUserSwitch();
    if (isLoaded.value) return;

    try {
        const expiryKey = getExpiryKey();
        const cartKey = getCartKey();
        const expiry = localStorage.getItem(expiryKey);
        if (expiry && Date.now() > parseInt(expiry)) {
            // Cart expired, clear it
            localStorage.removeItem(cartKey);
            localStorage.removeItem(expiryKey);
            cart.value = [];
        } else {
            const saved = localStorage.getItem(cartKey);
            if (saved) {
                cart.value = JSON.parse(saved);
            }
        }
    } catch (e) {
        console.error('Failed to load cart:', e);
        cart.value = [];
    }

    loadedForUser = getCurrentUserId();
    isLoaded.value = true;
};

// Save cart to localStorage
const saveCart = () => {
    try {
        const cartKey = getCartKey();
        const expiryKey = getExpiryKey();
        if (cart.value.length > 0) {
            localStorage.setItem(cartKey, JSON.stringify(cart.value));
            localStorage.setItem(expiryKey, String(Date.now() + CART_TTL));
        } else {
            localStorage.removeItem(cartKey);
            localStorage.removeItem(expiryKey);
        }
    } catch (e) {
        console.error('Failed to save cart:', e);
    }
};

// Watch for changes and save
watch(cart, saveCart, { deep: true });

export function useCart() {
    // Load on first use
    loadCart();
    
    const cartTotal = computed(() => 
        cart.value.reduce((sum, item) => sum + item.price, 0)
    );
    
    const cartItemCount = computed(() => cart.value.length);
    
    const addToCart = (item) => {
        cart.value.push(item);
    };
    
    const removeFromCart = (itemId) => {
        cart.value = cart.value.filter(item => item.id !== itemId);
    };
    
    const clearCart = () => {
        cart.value = [];
    };
    
    const setCart = (items) => {
        cart.value = items;
    };
    
    return {
        cart,
        cartTotal,
        cartItemCount,
        addToCart,
        removeFromCart,
        clearCart,
        setCart,
        loadCart,
    };
}
