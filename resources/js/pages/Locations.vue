<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../services/api';
import LocationCard from '../components/LocationCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import ErrorMessage from '../components/ErrorMessage.vue';
import Pagination from '../components/Pagination.vue';
import { usePagination } from '../composables/usePagination';

const locations = ref([]);
const loading = ref(true);
const error = ref(null);
const { currentPage, lastPage, total, updateFromResponse, goToPage } = usePagination();

const filters = ref({ name: '', type: '', dimension: '' });
let debounceTimer = null;

function debouncedFetch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        goToPage(1);
        fetchLocations();
    }, 300);
}

watch(() => filters.value.name, debouncedFetch);

async function fetchLocations() {
    loading.value = true;
    error.value = null;
    try {
        const params = { page: currentPage.value };
        if (filters.value.name) params.name = filters.value.name;
        if (filters.value.type) params.type = filters.value.type;
        if (filters.value.dimension) params.dimension = filters.value.dimension;

        const response = await api.get('/locations', { params });
        locations.value = response.data.data || [];
        updateFromResponse(response.data.meta || response.data);
    } catch (e) {
        error.value = e.response?.data?.error?.message || 'Failed to load locations.';
    } finally {
        loading.value = false;
    }
}

function handlePageChange(page) {
    goToPage(page);
    fetchLocations();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetFilters() {
    filters.value = { name: '', type: '', dimension: '' };
    goToPage(1);
    fetchLocations();
}

onMounted(fetchLocations);
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-white mb-6">Locations</h1>

        <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <input
                    v-model="filters.name"
                    type="text"
                    placeholder="Search by name..."
                    class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 transition-colors"
                />
                <input
                    v-model="filters.type"
                    @input="debouncedFetch"
                    type="text"
                    placeholder="Filter by type..."
                    class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 transition-colors"
                />
                <input
                    v-model="filters.dimension"
                    @input="debouncedFetch"
                    type="text"
                    placeholder="Filter by dimension..."
                    class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 transition-colors"
                />
                <button @click="resetFilters" class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors text-sm">
                    Reset
                </button>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />
        <ErrorMessage v-else-if="error" :message="error" @retry="fetchLocations" />
        <template v-else>
            <div v-if="locations.length === 0" class="text-center py-16">
                <p class="text-gray-500 text-lg">No locations found matching your filters.</p>
            </div>
            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <LocationCard v-for="location in locations" :key="location.id" :location="location" />
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
