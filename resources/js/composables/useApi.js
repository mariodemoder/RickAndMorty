import { ref } from 'vue';
import api from '../services/api';

export function useApi() {
    const data = ref(null);
    const loading = ref(false);
    const error = ref(null);

    async function fetch(url, params = {}) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get(url, { params });
            data.value = response.data;
            return response.data;
        } catch (e) {
            error.value = e.response?.data?.error?.message || e.message;
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function post(url, payload = {}) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(url, payload);
            data.value = response.data;
            return response.data;
        } catch (e) {
            error.value = e.response?.data?.error?.message || e.response?.data?.message || e.message;
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function del(url) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.delete(url);
            data.value = response.data;
            return response.data;
        } catch (e) {
            error.value = e.response?.data?.error?.message || e.message;
            throw e;
        } finally {
            loading.value = false;
        }
    }

    return { data, loading, error, fetch, post, del };
}
