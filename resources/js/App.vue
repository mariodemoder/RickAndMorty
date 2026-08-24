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
            <p class="mt-2">
                <a 
                    href="http://localhost:8080/api/documentation" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="text-gray-400 hover:text-white transition-colors underline"
                >
                    Documentación de la API
                </a>
            </p>
        </footer>
    </div>
</template>