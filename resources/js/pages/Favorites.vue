<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import CharacterCard from '../components/CharacterCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import ErrorMessage from '../components/ErrorMessage.vue';

const favorites = ref([]);
const loading = ref(true);
const error = ref(null);

async function fetchFavorites() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/favorites');
        favorites.value = response.data.data || [];
    } catch (e) {
        error.value = e.response?.data?.error?.message || 'Failed to load favorites.';
    } finally {
        loading.value = false;
    }
}

async function removeFavorite(characterId) {
    try {
        await api.delete(`/favorites/${characterId}`);
        favorites.value = favorites.value.filter(c => c.id !== characterId);
    } catch (e) {
        // silently handle
    }
}

onMounted(fetchFavorites);
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-white mb-6">My Favorites</h1>

        <LoadingSpinner v-if="loading" />
        <ErrorMessage v-else-if="error" :message="error" @retry="fetchFavorites" />
        <template v-else>
            <div v-if="favorites.length === 0" class="text-center py-16">
                <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <p class="text-gray-500 text-lg">You have no favorites yet.</p>
                <router-link to="/characters" class="inline-block mt-4 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition-colors">
                    Browse Characters
                </router-link>
            </div>
            <div v-else class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="character in favorites" :key="character.id" class="relative group">
                        <CharacterCard :character="character" />
                        <button
                            @click.stop="removeFavorite(character.id)"
                            class="absolute top-2 right-2 w-8 h-8 bg-red-600/90 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500"
                            title="Remove from favorites"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
