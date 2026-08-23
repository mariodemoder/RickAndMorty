<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import { useAuth } from '../composables/useAuth';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import ErrorMessage from '../components/ErrorMessage.vue';

const route = useRoute();
const { isAuthenticated } = useAuth();
const character = ref(null);
const loading = ref(true);
const error = ref(null);
const favLoading = ref(false);

async function fetchCharacter() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get(`/characters/${route.params.id}`);
        character.value = response.data.data || response.data;
    } catch (e) {
        error.value = e.response?.data?.error?.message || 'Failed to load character.';
    } finally {
        loading.value = false;
    }
}

async function addToFavorites() {
    favLoading.value = true;
    try {
        await api.post('/favorites', { character_id: character.value.id });
    } catch (e) {
        // silently handle
    } finally {
        favLoading.value = false;
    }
}

function statusColor(status) {
    switch (status) {
        case 'Alive': return 'bg-green-500';
        case 'Dead': return 'bg-red-500';
        default: return 'bg-gray-500';
    }
}

function statusTextColor(status) {
    switch (status) {
        case 'Alive': return 'text-green-400 bg-green-400/10';
        case 'Dead': return 'text-red-400 bg-red-400/10';
        default: return 'text-gray-400 bg-gray-400/10';
    }
}

watch(() => route.params.id, fetchCharacter);
onMounted(fetchCharacter);
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <router-link to="/characters" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-green-400 transition-colors mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Characters
        </router-link>

        <LoadingSpinner v-if="loading" />
        <ErrorMessage v-else-if="error" :message="error" @retry="fetchCharacter" />
        <div v-else-if="character" class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="md:flex">
                <div class="md:w-1/3">
                    <img :src="character.image" :alt="character.name" class="w-full h-full object-cover" />
                </div>
                <div class="p-6 md:w-2/3">
                    <div class="flex items-center gap-3 mb-4">
                        <h1 class="text-2xl font-bold text-white">{{ character.name }}</h1>
                        <span :class="[statusTextColor(character.status), 'px-2.5 py-0.5 rounded-full text-xs font-medium']">
                            {{ character.status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div>
                            <span class="text-gray-500">Species</span>
                            <p class="text-white">{{ character.species }}</p>
                        </div>
                        <div v-if="character.type">
                            <span class="text-gray-500">Type</span>
                            <p class="text-white">{{ character.type }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Gender</span>
                            <p class="text-white">{{ character.gender }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div v-if="character.origin">
                            <span class="text-gray-500 text-sm">Origin</span>
                            <router-link
                                v-if="character.origin?.id"
                                :to="{ name: 'location-detail', params: { id: character.origin.id } }"
                                class="block text-green-400 hover:text-green-300 transition-colors"
                            >
                                {{ character.origin.name }}
                            </router-link>
                            <p v-else class="text-white">{{ character.origin?.name || 'Unknown' }}</p>
                        </div>
                        <div v-if="character.location">
                            <span class="text-gray-500 text-sm">Current Location</span>
                            <router-link
                                v-if="character.location?.id"
                                :to="{ name: 'location-detail', params: { id: character.location.id } }"
                                class="block text-green-400 hover:text-green-300 transition-colors"
                            >
                                {{ character.location.name }}
                            </router-link>
                            <p v-else class="text-white">{{ character.location?.name || 'Unknown' }}</p>
                        </div>
                    </div>

                    <button
                        v-if="isAuthenticated"
                        @click="addToFavorites"
                        :disabled="favLoading"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 disabled:opacity-50 transition-colors text-sm font-medium"
                    >
                        {{ favLoading ? 'Adding...' : 'Add to Favorites' }}
                    </button>
                </div>
            </div>

            <div v-if="character.episodes && character.episodes.length > 0" class="border-t border-gray-800 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Episodes ({{ character.episodes.length }})</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <router-link
                        v-for="episode in character.episodes"
                        :key="episode.id"
                        :to="{ name: 'episode-detail', params: { id: episode.id } }"
                        class="flex items-center gap-3 p-3 bg-gray-800 rounded-lg hover:bg-gray-750 hover:border-gray-700 border border-gray-800 transition-colors"
                    >
                        <span class="text-xs font-mono text-green-400 bg-green-400/10 px-2 py-0.5 rounded shrink-0">{{ episode.episode_code }}</span>
                        <span class="text-sm text-gray-300 truncate">{{ episode.name }}</span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
