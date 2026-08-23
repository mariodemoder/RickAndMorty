<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { user, isAuthenticated, logout } = useAuth();
const mobileMenuOpen = ref(false);

async function handleLogout() {
    await logout();
    router.push({ name: 'home' });
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}
</script>

<template>
    <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-8">
                    <router-link to="/" class="text-xl font-bold text-green-400 hover:text-green-300 transition-colors" @click="closeMobileMenu">
                        R&M
                    </router-link>
                    <div class="hidden md:flex items-center space-x-1">
                        <router-link
                            v-for="link in [
                                { to: '/characters', label: 'Characters' },
                                { to: '/episodes', label: 'Episodes' },
                                { to: '/locations', label: 'Locations' },
                            ]"
                            :key="link.to"
                            :to="link.to"
                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"
                            active-class="!bg-gray-800 !text-white"
                        >
                            {{ link.label }}
                        </router-link>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-1">
                    <template v-if="isAuthenticated">
                        <router-link
                            to="/favorites"
                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"
                            active-class="!bg-gray-800 !text-white"
                        >
                            Favorites
                        </router-link>
                        <span class="text-gray-500 text-sm px-2">{{ user?.name }}</span>
                        <button
                            @click="handleLogout"
                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"
                        >
                            Logout
                        </button>
                    </template>
                    <template v-else>
                        <router-link
                            to="/login"
                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"
                            active-class="!bg-gray-800 !text-white"
                        >
                            Login
                        </router-link>
                        <router-link
                            to="/register"
                            class="ml-2 px-4 py-2 rounded-md text-sm font-medium bg-green-600 text-white hover:bg-green-500 transition-colors"
                        >
                            Register
                        </router-link>
                    </template>
                </div>
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-800 bg-gray-900">
            <div class="px-4 py-3 space-y-1">
                <router-link
                    v-for="link in [
                        { to: '/characters', label: 'Characters' },
                        { to: '/episodes', label: 'Episodes' },
                        { to: '/locations', label: 'Locations' },
                    ]"
                    :key="link.to"
                    :to="link.to"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800"
                    @click="closeMobileMenu"
                >
                    {{ link.label }}
                </router-link>
                <template v-if="isAuthenticated">
                    <router-link to="/favorites" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800" @click="closeMobileMenu">Favorites</router-link>
                    <button @click="handleLogout" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Logout</button>
                </template>
                <template v-else>
                    <router-link to="/login" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800" @click="closeMobileMenu">Login</router-link>
                    <router-link to="/register" class="block px-3 py-2 rounded-md text-base font-medium text-green-400 hover:text-green-300" @click="closeMobileMenu">Register</router-link>
                </template>
            </div>
        </div>
    </nav>
</template>
