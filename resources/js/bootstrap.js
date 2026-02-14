import axios from 'axios';
window.axios = axios;

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// CSRF token from meta tag as fallback
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
