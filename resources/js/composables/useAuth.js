import { ref, computed } from 'vue';
import api from '../services/api';

const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'));
const token = ref(localStorage.getItem('auth_token') || null);

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value);

    async function login(email, password) {
        const response = await api.post('/login', { email, password });
        const { user: userData, token: tokenData } = response.data;
        user.value = userData;
        token.value = tokenData;
        localStorage.setItem('auth_user', JSON.stringify(userData));
        localStorage.setItem('auth_token', tokenData);
        return response.data;
    }

    async function register(name, email, password, password_confirmation) {
        const response = await api.post('/register', {
            name,
            email,
            password,
            password_confirmation,
        });
        const { user: userData, token: tokenData } = response.data;
        user.value = userData;
        token.value = tokenData;
        localStorage.setItem('auth_user', JSON.stringify(userData));
        localStorage.setItem('auth_token', tokenData);
        return response.data;
    }

    async function logout() {
        try {
            await api.post('/logout');
        } finally {
            user.value = null;
            token.value = null;
            localStorage.removeItem('auth_user');
            localStorage.removeItem('auth_token');
        }
    }

    function loadFromStorage() {
        user.value = JSON.parse(localStorage.getItem('auth_user') || 'null');
        token.value = localStorage.getItem('auth_token') || null;
    }

    return {
        user,
        token,
        isAuthenticated,
        login,
        register,
        logout,
        loadFromStorage,
    };
}
