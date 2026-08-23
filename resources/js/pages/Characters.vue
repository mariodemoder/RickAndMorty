<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../services/api';
import CharacterCard from '../components/CharacterCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import ErrorMessage from '../components/ErrorMessage.vue';
import Pagination from '../components/Pagination.vue';
import { usePagination } from '../composables/usePagination';

const characters = ref([]);
const loading = ref(true);
const error = ref(null);
const { currentPage, lastPage, total, updateFromResponse, goToPage } = usePagination();

const filters = ref({
    name: '',
    status: '',
    species: '',
    gender: '',
});

let debounceTimer = null;

function debouncedFetch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        goToPage(1);
        fetchCharacters();
    }, 300);
}

watch(() => filters.value.name, debouncedFetch);

async function fetchCharacters() {
    loading.value = true;
    error.value = null;
    try {
        const params = { page: currentPage.value };
        if (filters.value.name) params.name = filters.value.name;
        if (filters.value.status) params.status = filters.value.status;
        if (filters.value.species) params.species = filters.value.species;
        if (filters.value.gender) params.gender = filters.value.gender;

        const response = await api.get('/characters', { params });
        characters.value = response.data.data || [];
        updateFromResponse(response.data.meta || response.data);
    } catch (e) {
        error.value = e.response?.data?.error?.message || 'Failed to load characters.';
    } finally {
        loading.value = false;
    }
}

function handlePageChange(page) {
    goToPage(page);
    fetchCharacters();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetFilters() {
    filters.value = { name: '', status: '', species: '', gender: '' };
    goToPage(1);
    fetchCharacters();
}

onMounted(fetchCharacters);
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-white mb-6">Characters</h1>

        <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <input
                    v-model="filters.name"
                    type="text"
                    placeholder="Search by name..."
                    class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 transition-colors"
                />
                <select
                    v-model="filters.status"
                    @change="fetchCharacters"
                    class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-green-500 transition-colors"
                >
                    <option value="">All Status</option>
                    <option value="Alive">Alive</option>
                    <option value="Dead">Dead</option>
                    <option value="unknown">Unknown</option>
                </select>
                <input
                    v-model="filters.species"
                    @input="debouncedFetch"
                    type="text"
                    placeholder="Filter by species..."
                    class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 transition-colors"
                />
                <div class="flex gap-2">
                    <select
                        v-model="filters.gender"
                        @change="fetchCharacters"
                        class="flex-1 px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-green-500 transition-colors"
                    >
                        <option value="">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Genderless">Genderless</option>
                        <option value="unknown">Unknown</option>
                    </select>
                    <button @click="resetFilters" class="px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors text-sm">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />
        <ErrorMessage v-else-if="error" :message="error" @retry="fetchCharacters" />
        <template v-else>
            <div v-if="characters.length === 0" class="text-center py-16">
                <p class="text-gray-500 text-lg">No characters found matching your filters.</p>
            </div>
            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <CharacterCard v-for="character in characters" :key="character.id" :character="character" />
                </div>
                <Pagination
                    :current-page="currentPage"
                    :last-page="lastPage"
                    :total="total"
                    @update:current-page="handlePageChange"
                />
            </template>
        </template>
    </div>
</template>
