<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import CharacterCard from '../components/CharacterCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import ErrorMessage from '../components/ErrorMessage.vue';

const route = useRoute();
const location = ref(null);
const loading = ref(true);
const error = ref(null);

async function fetchLocation() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get(`/locations/${route.params.id}`);
        location.value = response.data.data || response.data;
    } catch (e) {
        error.value = e.response?.data?.error?.message || 'Failed to load location.';
    } finally {
        loading.value = false;
    }
}

watch(() => route.params.id, fetchLocation);
onMounted(fetchLocation);
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <router-link to="/locations" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-green-400 transition-colors mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Locations
        </router-link>

        <LoadingSpinner v-if="loading" />
        <ErrorMessage v-else-if="error" :message="error" @retry="fetchLocation" />
        <div v-else-if="location">
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-8">
                <h1 class="text-2xl font-bold text-white">{{ location.name }}</h1>
                <div class="flex flex-wrap gap-4 mt-3 text-sm">
                    <div>
                        <span class="text-gray-500">Type:</span>
                        <span class="text-white ml-2">{{ location.type }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Dimension:</span>
                        <span class="text-white ml-2">{{ location.dimension }}</span>
                    </div>
                </div>
            </div>

            <div v-if="location.residents && location.residents.length > 0">
                <h2 class="text-lg font-semibold text-white mb-4">Residents ({{ location.residents.length }})</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <CharacterCard v-for="resident in location.residents" :key="resident.id" :character="resident" />
                </div>
            </div>
            <div v-else class="text-center py-12">
                <p class="text-gray-500">No known residents for this location.</p>
            </div>
        </div>
    </div>
</template>
