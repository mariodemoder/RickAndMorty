<script setup>
defineProps({
    character: { type: Object, required: true },
});

function statusColor(status) {
    switch (status) {
        case 'Alive': return 'bg-green-500';
        case 'Dead': return 'bg-red-500';
        default: return 'bg-gray-500';
    }
}
</script>

<template>
    <router-link
        :to="{ name: 'character-detail', params: { id: character.id } }"
        class="block bg-gray-900 rounded-xl overflow-hidden border border-gray-800 hover:border-green-500/50 hover:shadow-lg hover:shadow-green-500/10 transition-all duration-300 group"
    >
        <div class="aspect-square overflow-hidden">
            <img
                :src="character.image"
                :alt="character.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            />
        </div>
        <div class="p-4">
            <h3 class="font-semibold text-white truncate group-hover:text-green-400 transition-colors">{{ character.name }}</h3>
            <div class="flex items-center gap-2 mt-1.5">
                <span :class="[statusColor(character.status), 'w-2 h-2 rounded-full inline-block']"></span>
                <span class="text-sm text-gray-400">{{ character.status }} &mdash; {{ character.species }}</span>
            </div>
        </div>
    </router-link>
</template>
