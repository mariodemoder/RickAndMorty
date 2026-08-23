<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { login } = useAuth();

const form = ref({ email: '', password: '' });
const loading = ref(false);
const error = ref(null);

async function handleSubmit() {
    loading.value = true;
    error.value = null;
    try {
        await login(form.value.email, form.value.password);
        router.push({ name: 'favorites' });
    } catch (e) {
        error.value = e.response?.data?.error?.message || 'Invalid credentials.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <h1 class="text-3xl font-bold text-center text-white mb-8">Login</h1>
            <form @submit.prevent="handleSubmit" class="bg-gray-900 rounded-xl p-8 border border-gray-800 space-y-5">
                <div v-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-red-400 text-sm">
                    {{ error }}
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-colors"
                        placeholder="you@example.com"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-colors"
                        placeholder="Your password"
                    />
                </div>
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    {{ loading ? 'Logging in...' : 'Login' }}
                </button>
                <p class="text-center text-sm text-gray-500">
                    Don't have an account?
                    <router-link to="/register" class="text-green-400 hover:text-green-300">Register</router-link>
                </p>
            </form>
        </div>
    </div>
</template>
