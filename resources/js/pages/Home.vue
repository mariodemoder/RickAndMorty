<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import CharacterCard from '../components/CharacterCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';

const stats = ref({ characters: 0, episodes: 0, locations: 0 });
const randomCharacter = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const [charRes, epRes, locRes] = await Promise.all([
            api.get('/characters', { params: { per_page: 1 } }),
            api.get('/episodes', { params: { per_page: 1 } }),
            api.get('/locations', { params: { per_page: 1 } }),
        ]);
        stats.value = {
            characters: charRes.data.meta?.total || charRes.data.total || 0,
            episodes: epRes.data.meta?.total || epRes.data.total || 0,
            locations: locRes.data.meta?.total || locRes.data.total || 0,
        };

        const randomId = Math.floor(Math.random() * 826) + 1;
        const charRes2 = await api.get(`/characters/${randomId}`);
        randomCharacter.value = charRes2.data.data || charRes2.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <section class="relative bg-gradient-to-b from-green-900/20 to-gray-950 py-20 lg:py-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold">
                    <span class="text-green-400">Rick</span> & <span class="text-blue-400">Morty</span> Explorer
                </h1>
                <p class="mt-4 text-lg text-gray-400 max-w-2xl mx-auto">
                    Discover every character, episode, and location from the multiverse.
                </p>
                <router-link
                    to="/characters"
                    class="inline-block mt-8 px-8 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-500 transition-colors"
                >
                    Explore Characters
                </router-link>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <LoadingSpinner v-if="loading" />
            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-16">
                    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800 text-center">
                        <div class="text-3xl font-bold text-green-400">{{ stats.characters }}</div>
                        <div class="text-gray-400 mt-1">Characters</div>
                    </div>
                    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800 text-center">
                        <div class="text-3xl font-bold text-blue-400">{{ stats.episodes }}</div>
                        <div class="text-gray-400 mt-1">Episodes</div>
                    </div>
                    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800 text-center">
                        <div class="text-3xl font-bold text-purple-400">{{ stats.locations }}</div>
                        <div class="text-gray-400 mt-1">Locations</div>
                    </div>
                </div>

                <div v-if="randomCharacter">
                    <h2 class="text-2xl font-bold text-white mb-6">Featured Character</h2>
                    <div class="max-w-sm mx-auto">
                        <CharacterCard :character="randomCharacter" />
                    </div>
                </div>
            </template>
        </section>
    </div>
</template>
