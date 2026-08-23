<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import CharacterCard from '../components/CharacterCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import ErrorMessage from '../components/ErrorMessage.vue';

const route = useRoute();
const episode = ref(null);
const loading = ref(true);
const error = ref(null);

async function fetchEpisode() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get(`/episodes/${route.params.id}`);
        episode.value = response.data.data || response.data;
    } catch (e) {
        error.value = e.response?.data?.error?.message || 'Failed to load episode.';
    } finally {
        loading.value = false;
    }
}

watch(() => route.params.id, fetchEpisode);
onMounted(fetchEpisode);
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <router-link to="/episodes" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-green-400 transition-colors mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Episodes
        </router-link>

        <LoadingSpinner v-if="loading" />
        <ErrorMessage v-else-if="error" :message="error" @retry="fetchEpisode" />
        <div v-else-if="episode">
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-8">
                <span class="text-xs font-mono text-green-400 bg-green-400/10 px-2.5 py-1 rounded">{{ episode.episode_code }}</span>
                <h1 class="text-2xl font-bold text-white mt-3">{{ episode.name }}</h1>
                <p class="text-gray-400 mt-2">Aired: {{ episode.air_date }}</p>
            </div>

            <div v-if="episode.characters && episode.characters.length > 0">
                <h2 class="text-lg font-semibold text-white mb-4">Characters ({{ episode.characters.length }})</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <CharacterCard v-for="char in episode.characters" :key="char.id" :character="char" />
                </div>
            </div>
        </div>
    </div>
</template>
