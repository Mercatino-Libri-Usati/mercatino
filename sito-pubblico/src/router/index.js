import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/home',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
  },
  {
    path: '/servizi',
    name: 'servizi',
    component: () => import('@/views/ServiziView.vue'),
  },
  {
    path: '/adozioni',
    name: 'adozioni',
    component: () => import('@/views/AdozioniView.vue'),
  },
  {
    path: '/area-utente',
    name: 'area-utente',
    component: () => import('@/views/AreaUtenteView.vue'),
  },
  {
    path: '/chi-siamo',
    name: 'chi-siamo',
    component: () => import('@/views/ChiSiamoView.vue'),
  },
  {
    path: '/registrazione',
    name: 'registrazione',
    component: () => import('@/views/RegistrazioneView.vue'),
  },
  {
    path: '/',
    redirect: '/home',
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'error',
    component: () => import('@/views/Error404View.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
