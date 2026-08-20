<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Sync Logs</h1>

    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
      <p class="mt-4 text-gray-500">Loading sync logs...</p>
    </div>

    <div v-else-if="logs.length === 0" class="text-center py-12 bg-gray-50 rounded-lg">
      <p class="text-gray-500 text-lg">No sync logs yet.</p>
      <p class="text-gray-400 mt-2">Run <code class="bg-gray-200 px-2 py-1 rounded">php artisan sync:rick-and-morty</code> to generate logs.</p>
    </div>

    <div v-else>
      <div
        v-for="log in logs"
        :key="log.id"
        class="bg-white border rounded-lg mb-4 overflow-hidden"
      >
        <!-- Header -->
        <div
          class="px-6 py-4 cursor-pointer flex items-center justify-between"
          :class="statusClass(log.status)"
          @click="toggleExpand(log.id)"
        >
          <div class="flex items-center gap-4">
            <span
              class="px-3 py-1 rounded-full text-sm font-semibold text-white"
              :class="statusBadge(log.status)"
            >
              {{ log.status.toUpperCase() }}
            </span>
            <span class="text-gray-700 font-medium">
              {{ formatDate(log.started_at) }}
            </span>
            <span v-if="log.duration" class="text-gray-500 text-sm">
              {{ log.duration }}s
            </span>
          </div>
          <div class="flex items-center gap-6 text-sm">
            <span class="text-blue-600">
              <strong>{{ log.locations_count }}</strong> locations
            </span>
            <span class="text-green-600">
              <strong>{{ log.episodes_count }}</strong> episodes
            </span>
            <span class="text-purple-600">
              <strong>{{ log.characters_count }}</strong> characters
            </span>
            <svg
              class="w-5 h-5 text-gray-400 transition-transform"
              :class="{ 'rotate-180': expanded[log.id] }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Error message -->
        <div v-if="log.error_message" class="px-6 py-3 bg-red-50 border-t border-red-200">
          <p class="text-red-700 text-sm">
            <strong>Error:</strong> {{ log.error_message }}
          </p>
        </div>

        <!-- Entries (expandable) -->
        <div v-if="expanded[log.id]" class="border-t">
          <div v-if="log.entries && log.entries.length > 0">
            <div
              v-for="entry in log.entries"
              :key="entry.id"
              class="px-6 py-2 border-b last:border-b-0 text-sm font-mono"
              :class="entryLevelClass(entry.level)"
            >
              <span class="text-gray-400 mr-3">{{ formatTime(entry.created_at) }}</span>
              <span
                class="px-1.5 py-0.5 rounded text-xs font-bold mr-2"
                :class="entryBadge(entry.level)"
              >
                {{ entry.level.toUpperCase() }}
              </span>
              <span>{{ entry.message }}</span>
            </div>
          </div>
          <div v-else class="px-6 py-4 text-gray-400 text-sm">
            No detailed entries available.
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-8">
        <button
          v-for="page in totalPages"
          :key="page"
          @click="fetchLogs(page)"
          class="px-4 py-2 rounded-lg text-sm font-medium"
          :class="page === currentPage ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const logs = ref([])
const loading = ref(true)
const expanded = ref({})
const currentPage = ref(1)
const totalPages = ref(1)

const fetchLogs = async (page = 1) => {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/sync/logs?page=${page}`)
    logs.value = data.data
    currentPage.value = data.current_page
    totalPages.value = data.last_page
  } catch (error) {
    console.error('Failed to fetch sync logs:', error)
  } finally {
    loading.value = false
  }
}

const toggleExpand = (id) => {
  expanded.value[id] = !expanded.value[id]
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString()
}

const formatTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleTimeString()
}

const statusClass = (status) => {
  return {
    running: 'bg-yellow-50',
    completed: 'bg-green-50',
    failed: 'bg-red-50',
  }[status] || 'bg-gray-50'
}

const statusBadge = (status) => {
  return {
    running: 'bg-yellow-500',
    completed: 'bg-green-500',
    failed: 'bg-red-500',
  }[status] || 'bg-gray-500'
}

const entryLevelClass = (level) => {
  return {
    info: 'bg-white',
    warning: 'bg-yellow-50',
    error: 'bg-red-50',
  }[level] || 'bg-white'
}

const entryBadge = (level) => {
  return {
    info: 'bg-blue-100 text-blue-700',
    warning: 'bg-yellow-100 text-yellow-700',
    error: 'bg-red-100 text-red-700',
  }[level] || 'bg-gray-100 text-gray-700'
}

onMounted(() => fetchLogs())
</script>
