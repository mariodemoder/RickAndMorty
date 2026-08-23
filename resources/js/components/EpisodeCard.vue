<script setup>
defineProps({
    episode: { type: Object, required: true },
});
</script>

<template>
    <router-link
        :to="{ name: 'episode-detail', params: { id: episode.id } }"
        class="block bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-green-500/50 hover:shadow-lg hover:shadow-green-500/10 transition-all duration-300 group"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="text-xs font-mono text-green-400 bg-green-400/10 px-2 py-0.5 rounded">{{ episode.episode_code }}</span>
                <h3 class="font-semibold text-white mt-2 truncate group-hover:text-green-400 transition-colors">{{ episode.name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ episode.air_date }}</p>
            </div>
            <svg class="w-5 h-5 text-gray-600 group-hover:text-green-400 transition-colors shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </router-link>
</template>
