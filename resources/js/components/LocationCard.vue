<script setup>
defineProps({
    location: { type: Object, required: true },
});
</script>

<template>
    <router-link
        :to="{ name: 'location-detail', params: { id: location.id } }"
        class="block bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-green-500/50 hover:shadow-lg hover:shadow-green-500/10 transition-all duration-300 group"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="font-semibold text-white truncate group-hover:text-green-400 transition-colors">{{ location.name }}</h3>
                <p class="text-sm text-gray-400 mt-1">{{ location.type }}</p>
                <p class="text-sm text-gray-500 mt-0.5">{{ location.dimension }}</p>
            </div>
            <svg class="w-5 h-5 text-gray-600 group-hover:text-green-400 transition-colors shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </router-link>
</template>
