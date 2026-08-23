<script setup>
const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    total: { type: Number, default: 0 },
});

const emit = defineEmits(['update:currentPage']);

function goTo(page) {
    if (page >= 1 && page <= props.lastPage) {
        emit('update:currentPage', page);
    }
}
</script>

<template>
    <div v-if="lastPage > 1" class="flex items-center justify-center gap-2 py-8">
        <button
            @click="goTo(currentPage - 1)"
            :disabled="currentPage <= 1"
            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-800 text-gray-300 hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        >
            Previous
        </button>
        <template v-for="page in lastPage" :key="page">
            <button
                v-if="page === 1 || page === lastPage || (page >= currentPage - 1 && page <= currentPage + 1)"
                @click="goTo(page)"
                class="w-9 h-9 rounded-lg text-sm font-medium transition-colors"
                :class="page === currentPage ? 'bg-green-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'"
            >
                {{ page }}
            </button>
            <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="text-gray-600">...</span>
        </template>
        <button
            @click="goTo(currentPage + 1)"
            :disabled="currentPage >= lastPage"
            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-800 text-gray-300 hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        >
            Next
        </button>
    </div>
</template>
