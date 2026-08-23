<script setup>
defineProps({
    message: { type: String, default: 'Something went wrong.' },
});

defineEmits(['retry']);
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <svg class="w-12 h-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <p class="text-gray-400 mb-4">{{ message }}</p>
        <button
            v-if="$attrs.onRetry"
            @click="$emit('retry')"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition-colors text-sm"
        >
            Try again
        </button>
    </div>
</template>
