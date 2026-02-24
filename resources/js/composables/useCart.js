import { ref, computed, watch } from 'vue';

const CART_KEY = 'hm_cart';
const CART_EXPIRY_KEY = 'hm_cart_expiry';
const CART_TTL = 24 * 60 * 60 * 1000; // 24 hours

// Shared reactive state
const cart = ref([]);
const isLoaded = ref(false);

// Load cart from localStorage
const loadCart = () => {
    if (isLoaded.value) return;
    
    try {
        const expiry = localStorage.getItem(CART_EXPIRY_KEY);
        if (expiry && Date.now() > parseInt(expiry)) {
            // Cart expired, clear it
            localStorage.removeItem(CART_KEY);
            localStorage.removeItem(CART_EXPIRY_KEY);
            cart.value = [];
        } else {
            const saved = localStorage.getItem(CART_KEY);
            if (saved) {
                cart.value = JSON.parse(saved);
            }
        }
    } catch (e) {
        console.error('Failed to load cart:', e);
        cart.value = [];
    }
    
    isLoaded.value = true;
};

// Save cart to localStorage
const saveCart = () => {
    try {
        if (cart.value.length > 0) {
            localStorage.setItem(CART_KEY, JSON.stringify(cart.value));
            localStorage.setItem(CART_EXPIRY_KEY, String(Date.now() + CART_TTL));
        } else {
            localStorage.removeItem(CART_KEY);
            localStorage.removeItem(CART_EXPIRY_KEY);
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
