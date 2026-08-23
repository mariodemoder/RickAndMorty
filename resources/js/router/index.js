import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../pages/Home.vue'),
    },
    {
        path: '/characters',
        name: 'characters',
        component: () => import('../pages/Characters.vue'),
    },
    {
        path: '/characters/:id',
        name: 'character-detail',
        component: () => import('../pages/CharacterDetail.vue'),
        props: true,
    },
    {
        path: '/episodes',
        name: 'episodes',
        component: () => import('../pages/Episodes.vue'),
    },
    {
        path: '/episodes/:id',
        name: 'episode-detail',
        component: () => import('../pages/EpisodeDetail.vue'),
        props: true,
    },
    {
        path: '/locations',
        name: 'locations',
        component: () => import('../pages/Locations.vue'),
    },
    {
        path: '/locations/:id',
        name: 'location-detail',
        component: () => import('../pages/LocationDetail.vue'),
        props: true,
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/Login.vue'),
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../pages/Register.vue'),
    },
    {
        path: '/favorites',
        name: 'favorites',
        component: () => import('../pages/Favorites.vue'),
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach((to) => {
    const token = localStorage.getItem('auth_token');
    if (to.meta.requiresAuth && !token) {
        return { name: 'login' };
    }
});

export default router;
