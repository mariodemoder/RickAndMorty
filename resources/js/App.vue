<script setup>
import Navbar from './components/Navbar.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <Navbar />
        <main class="flex-1">
            <router-view />
        </main>
        <footer class="bg-gray-900 border-t border-gray-800 py-6 text-center text-gray-500 text-sm">
            <p>Rick & Morty Explorer &mdash; Built with Laravel + Vue.js</p>
        </footer>
    </div>
</template>
