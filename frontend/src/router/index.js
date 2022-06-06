import { createRouter, createWebHistory } from '@ionic/vue-router';
import store from '@/store';
import Tabs from '@/views/Tabs';
import CompetitionFormTabs from '@/views/competition/FormTabs';

// Definiraj routeeve za aplikaciju.
// Početna strana aplikacije vodi na tab s natjecanjima.
// meta "guest" govori da je route dostupan samo korisnicima koji nisu prijavljeni.
// meta "auth" govori da je za route potrebna prijava.
// meta "hideTabBar" govori da se izbornik ne prikazuje na routeu.

const routes = [
    {
        path: '/',
        name: 'home',
        redirect: '/tabs/competitions',
        meta: { guest: true },
        component: () => import('@/views/Home.vue')
    },
    {
        path: '/login',
        name: 'login',
        meta: { guest: true },
        component: () => import('@/views/Login.vue')
    },
    {
        path: '/tabs/',
        component: Tabs,
        children: [
            {
                path: '',
                redirect: 'competitions'
            },
            {
                path: 'competitions',
                name: 'competitions',
                meta: { main: true },
                component: () => import('@/views/competition/Index.vue')
            },
            {
                path: 'competitions/view/:id',
                name: 'view-competition',
                component: () => import('@/views/competition/View.vue')
            },
            {
                path: 'competitions/form',
                name: 'competition-form',
                redirect: '/tabs/competitions/form/general',
                meta: { auth: true },
                component: CompetitionFormTabs,
                children: [
                    {
                        path: 'general/:id?',
                        name: 'competition-form-general',
                        meta: { step: 'general', hideTabBar: true },
                        component: () => import('@/views/competition/FormGeneral.vue')
                    },
                    {
                        path: 'judges/:id',
                        name: 'competition-form-judges',
                        meta: { step: 'judges', hideTabBar: true },
                        component: () => import('@/views/competition/FormJudges.vue')
                    },
                    {
                        path: 'participants/:id',
                        name: 'competition-form-participants',
                        meta: { step: 'participants', hideTabBar: true },
                        component: () => import('@/views/competition/FormParticipants.vue')
                    },
                    {
                        path: 'results/:id',
                        name: 'competition-form-results',
                        meta: { step: 'results', hideTabBar: true },
                        component: () => import('@/views/competition/FormResults.vue')
                    }
                ]
            },
            {
                path: 'users',
                name: 'users',
                meta: { auth: true, main: true },
                component: () => import('@/views/user/Index.vue')
            },
            {
                path: 'users/view/:id',
                name: 'view-user',
                meta: { auth: true },
                component: () => import('@/views/user/View.vue')
            },
            {
                path: 'users/add',
                name: 'add-user',
                meta: { auth: true, hideTabBar: true },
                component: () => import('@/views/user/Form.vue')
            },
            {
                path: 'users/edit/:id',
                name: 'edit-user',
                meta: { auth: true, hideTabBar: true },
                component: () => import('@/views/user/Form.vue')
            },
            {
                path: 'clubs',
                name: 'clubs',
                meta: { auth: true, main: true },
                component: () => import('@/views/club/Index.vue')
            },
            {
                path: 'clubs/view/:id',
                name: 'view-club',
                meta: { auth: true },
                component: () => import('@/views/club/View.vue')
            },
            {
                path: 'clubs/add',
                name: 'add-club',
                meta: { auth: true, hideTabBar: true },
                component: () => import('@/views/club/Form.vue')
            },
            {
                path: 'clubs/edit/:id',
                name: 'edit-club',
                meta: { auth: true, hideTabBar: true },
                component: () => import('@/views/club/Form.vue')
            },
            {
                path: 'leaderboard',
                meta: { main: true },
                component: () => import('@/views/Leaderboard.vue')
            },
            {
                path: 'fake',
                component: () => import('@/views/competition/Index.vue')
            }
        ],
    }
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes
});

router.beforeEach(async (to, _from, next) => {
    // Prijavi korisnika ako je pronađen token.
    await store.dispatch('auth/loginFromStorage');
    
    // Zabrani određene routeve ovisno o tome je li korisnik prijavljen.
    const loggedIn = store.getters['auth/isLoggedIn'];
    if(to.matched.some(r => r.meta.auth) && !loggedIn) return next({ name: 'login' });
    if(to.matched.some(r => r.meta.guest) && loggedIn) return next({ name: 'competitions' });

    next();
})

export default router;